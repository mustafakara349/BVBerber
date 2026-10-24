<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTimeBlock;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeTimeBlockController extends Controller
{
    public function index(Request $request)
    {
        // Tüm blokları çek ve employee, ardından tarihe göre artan (asc) sırala
        $rawBlocks = EmployeeTimeBlock::with('employee.user')
            ->when($request->filled('employee_id'), function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_to);
            })
            ->orderBy('employee_id')
            ->orderBy('date', 'asc')
            ->get();

        $groupedBlocks = collect();
        $currentGroup = null;

        foreach ($rawBlocks as $block) {
            $blockDate = Carbon::parse($block->date)->format('Y-m-d');
            $blockStart = $block->start_time ? Carbon::parse($block->start_time)->format('H:i') : null;
            $blockEnd = $block->end_time ? Carbon::parse($block->end_time)->format('H:i') : null;

            if (!$currentGroup) {
                $currentGroup = clone $block;
                $currentGroup->safe_start = $blockStart;
                $currentGroup->safe_end = $blockEnd;
                $currentGroup->end_date_for_ui = $blockDate;
                $currentGroup->grouped_ids = [$block->id];
                continue;
            }

            $isSameProperties = (
                $currentGroup->employee_id == $block->employee_id &&
                $currentGroup->type == $block->type &&
                $currentGroup->safe_start === $blockStart &&
                $currentGroup->safe_end === $blockEnd &&
                $currentGroup->reason === $block->reason
            );

            $expectedNextDate = Carbon::parse($currentGroup->end_date_for_ui)->addDay()->format('Y-m-d');

            if ($isSameProperties && $blockDate === $expectedNextDate) {
                $currentGroup->end_date_for_ui = $blockDate;
                $grouped_ids = $currentGroup->grouped_ids;
                $grouped_ids[] = $block->id;
                $currentGroup->grouped_ids = $grouped_ids;
            } else {
                $groupedBlocks->push($currentGroup);
                
                $currentGroup = clone $block;
                $currentGroup->safe_start = $blockStart;
                $currentGroup->safe_end = $blockEnd;
                $currentGroup->end_date_for_ui = $blockDate;
                $currentGroup->grouped_ids = [$block->id];
            }
        }
        
        if ($currentGroup) {
            $groupedBlocks->push($currentGroup);
        }

        $now = Carbon::now();

        foreach ($groupedBlocks as $group) {
            $startDate = Carbon::parse($group->date);
            $endDate = Carbon::parse($group->end_date_for_ui);

            if ($group->type === 'full_day') {
                $startDateTime = $startDate->copy()->startOfDay();
                $endDateTime = $endDate->copy()->endOfDay();
            } else {
                $startTimeStr = ($group->safe_start ?? '00:00') . ':00';
                $endTimeStr = ($group->safe_end ?? '23:59') . ':59';
                $startDateTime = Carbon::parse($startDate->format('Y-m-d') . ' ' . $startTimeStr);
                $endDateTime = Carbon::parse($endDate->format('Y-m-d') . ' ' . $endTimeStr);
            }

            $group->start_datetime = $startDateTime;
            $group->end_datetime = $endDateTime;

            if ($endDateTime->lt($now)) {
                $group->status_key = 'past';
                $group->status_label = 'Tamamlandı';
                $group->status_class = 'secondary';
                $group->status_icon = 'ti-history';
            } else {
                $isCurrentlyActive = false;
                $todayStr = $now->format('Y-m-d');
                $startStr = $startDate->format('Y-m-d');
                $endStr = $endDate->format('Y-m-d');

                if ($todayStr >= $startStr && $todayStr <= $endStr) {
                    if ($group->type === 'full_day') {
                        $isCurrentlyActive = true;
                    } else {
                        $currentTimeStr = $now->format('H:i:s');
                        if ($currentTimeStr >= $startTimeStr && $currentTimeStr <= $endTimeStr) {
                            $isCurrentlyActive = true;
                        }
                    }
                }

                if ($isCurrentlyActive) {
                    $group->status_key = 'active';
                    $group->status_label = 'Aktif';
                    $group->status_class = 'success';
                    $group->status_icon = 'ti-activity';
                } else {
                    $group->status_key = 'upcoming';
                    $group->status_label = 'Planlandı';
                    $group->status_class = 'primary';
                    $group->status_icon = 'ti-calendar-time';
                }
            }
        }

        $activeBlocks = $groupedBlocks->filter(function ($item) {
            return $item->status_key !== 'past';
        })->sortBy(function ($item) {
            return $item->date . ' ' . ($item->safe_start ?? '00:00');
        })->values();

        $pastBlocks = $groupedBlocks->filter(function ($item) {
            return $item->status_key === 'past';
        })->sortByDesc(function ($item) {
            return $item->date . ' ' . ($item->safe_start ?? '00:00');
        })->values();

        // Tüm blokları modallar ve genel referanslar için birleştir
        $timeBlocks = $activeBlocks->concat($pastBlocks);
        $employees = \App\Models\Employee::with('user')->active()->get();

        return view('employee_time_blocks.index', compact('activeBlocks', 'pastBlocks', 'timeBlocks', 'employees'));
    }

    private function checkConflict($employeeId, $date, $type, $startTime = null, $endTime = null, $ignoreBlockIds = [])
    {
        // 1. Randevu Çakışması Kontrolü
        $aptQuery = Appointment::where('employee_id', $employeeId)
            ->whereDate('start_at', $date)
            ->whereIn('status', ['pending', 'confirmed']);

        if ($type === 'partial_time') {
            $startDateTime = $date . ' ' . $startTime;
            $endDateTime = $date . ' ' . $endTime;

            $aptQuery->where(function($q) use ($startDateTime, $endDateTime) {
                $q->whereBetween('start_at', [$startDateTime, $endDateTime])
                  ->orWhereBetween('end_at', [$startDateTime, $endDateTime])
                  ->orWhere(function($subq) use ($startDateTime, $endDateTime) {
                      $subq->where('start_at', '<', $startDateTime)
                           ->where('end_at', '>', $endDateTime);
                  });
            });
        }
        $conflictingAppointments = $aptQuery->count();
        if ($conflictingAppointments > 0) {
            return "Bu saatler arasında {$conflictingAppointments} adet aktif randevu bulunmaktadır.";
        }

        // 2. Diğer İzinlerle (Bloklarla) Çakışma Kontrolü
        $blockQuery = EmployeeTimeBlock::where('employee_id', $employeeId)
            ->where('date', $date);

        if (!empty($ignoreBlockIds)) {
            $blockQuery->whereNotIn('id', (array)$ignoreBlockIds);
        }

        $existingBlocks = $blockQuery->get();

        foreach ($existingBlocks as $block) {
            if ($type === 'full_day' || $block->type === 'full_day') {
                return "Bu güne ait zaten tam gün veya saatlik kısıtlama mevcut.";
            }

            if ($type === 'partial_time' && $block->type === 'partial_time') {
                $newStart = Carbon::parse($date . ' ' . $startTime);
                $newEnd = Carbon::parse($date . ' ' . $endTime);
                $existingStart = Carbon::parse($date . ' ' . $block->start_time);
                $existingEnd = Carbon::parse($date . ' ' . $block->end_time);

                // Çakışma mantığı: Start1 < End2 && End1 > Start2
                if ($newStart < $existingEnd && $newEnd > $existingStart) {
                    return "Bu saat aralığı başka bir saatlik kısıtlama ile çakışıyor ({$block->start_time} - {$block->end_time}).";
                }
            }
        }

        return null;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:full_day,partial_time',
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'start_time' => 'required_if:type,partial_time',
            'end_time' => 'required_if:type,partial_time',
            'reason' => 'nullable|string|max:255',
        ]);

        $startDate = Carbon::parse($validated['date']);
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy();

        $datesToProcess = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $datesToProcess[] = $date->format('Y-m-d');
        }

        foreach ($datesToProcess as $currentDate) {
            $conflictError = $this->checkConflict(
                $validated['employee_id'], 
                $currentDate, 
                $validated['type'], 
                $validated['start_time'] ?? null, 
                $validated['end_time'] ?? null
            );

            if ($conflictError) {
                $errorMsg = (count($datesToProcess) > 1) 
                    ? Carbon::parse($currentDate)->format('d.m.Y') . " tarihinde hata: " . $conflictError 
                    : $conflictError;

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return back()->with('error', $errorMsg);
            }
        }

        foreach ($datesToProcess as $currentDate) {
            EmployeeTimeBlock::create([
                'employee_id' => $validated['employee_id'],
                'type' => $validated['type'],
                'date' => $currentDate,
                'start_time' => $validated['type'] === 'partial_time' ? $validated['start_time'] : null,
                'end_time' => $validated['type'] === 'partial_time' ? $validated['end_time'] : null,
                'reason' => $validated['reason'],
                'created_by' => auth()->id(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Kısıtlamalar başarıyla eklendi.']);
        }
        return back()->with('success', 'Kısıtlamalar başarıyla eklendi.');
    }

    public function update(Request $request, EmployeeTimeBlock $employeeTimeBlock)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:full_day,partial_time',
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'start_time' => 'required_if:type,partial_time',
            'end_time' => 'required_if:type,partial_time',
            'reason' => 'nullable|string|max:255',
        ]);

        // Formdan grouped_ids geldiyse onları dizi yap
        $oldIds = $request->input('grouped_ids') 
            ? explode(',', $request->input('grouped_ids')) 
            : [$employeeTimeBlock->id];

        $startDate = Carbon::parse($validated['date']);
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy();

        $datesToProcess = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $datesToProcess[] = $date->format('Y-m-d');
        }

        foreach ($datesToProcess as $currentDate) {
            $conflictError = $this->checkConflict(
                $validated['employee_id'], 
                $currentDate, 
                $validated['type'], 
                $validated['start_time'] ?? null, 
                $validated['end_time'] ?? null,
                $oldIds // Kendi eski id'lerini çakışmadan muaf tut
            );

            if ($conflictError) {
                $errorMsg = (count($datesToProcess) > 1) 
                    ? Carbon::parse($currentDate)->format('d.m.Y') . " tarihinde hata: " . $conflictError 
                    : $conflictError;

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return back()->with('error', $errorMsg);
            }
        }

        // Çakışma yoksa eski kayıt serisini tamamen temizle
        EmployeeTimeBlock::whereIn('id', $oldIds)->delete();

        // Yeni seriyi oluştur
        foreach ($datesToProcess as $currentDate) {
            EmployeeTimeBlock::create([
                'employee_id' => $validated['employee_id'],
                'type' => $validated['type'],
                'date' => $currentDate,
                'start_time' => $validated['type'] === 'partial_time' ? $validated['start_time'] : null,
                'end_time' => $validated['type'] === 'partial_time' ? $validated['end_time'] : null,
                'reason' => $validated['reason'],
                'created_by' => auth()->id(), // Yapanı koru veya yenile
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Kısıtlama başarıyla güncellendi.']);
        }
        return back()->with('success', 'Kısıtlama başarıyla güncellendi.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'quick_action' => 'required|in:today_rest,tomorrow,next_xdays',
            'days_count' => 'required_if:quick_action,next_xdays|nullable|integer|min:1|max:30',
        ]);

        $employeeId = $validated['employee_id'];
        $action = $validated['quick_action'];
        $now = now();
        
        $type = 'full_day';
        $startDate = null;
        $endDate = null;
        $startTime = null;
        $endTime = null;
        $reason = 'Hızlı İşlem ile kapatıldı.';

        if ($action === 'today_rest') {
            $type = 'partial_time';
            $startDate = $now->format('Y-m-d');
            $endDate = $startDate;
            $startTime = $now->format('H:i');
            $endTime = '23:59';
            $reason = 'Günün geri kalanı için randevuya kapalı';
        } elseif ($action === 'tomorrow') {
            $type = 'full_day';
            $startDate = $now->copy()->addDay()->format('Y-m-d');
            $endDate = $startDate;
            $reason = 'Yarın (Tüm gün) için kapalı';
        } elseif ($action === 'next_xdays') {
            $type = 'full_day';
            $days = $validated['days_count'];
            $startDate = $now->copy()->format('Y-m-d'); // Bugünden itibaren
            $endDate = $now->copy()->addDays($days - 1)->format('Y-m-d'); 
            $reason = "Önümüzdeki {$days} gün için kapalı";
        }

        $datesToProcess = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $datesToProcess[] = $date->format('Y-m-d');
        }

        // Çakışma Kontrolü
        foreach ($datesToProcess as $currentDate) {
            $conflictError = $this->checkConflict(
                $employeeId, 
                $currentDate, 
                $type, 
                $startTime, 
                $endTime
            );

            if ($conflictError) {
                $errorMsg = (count($datesToProcess) > 1) 
                    ? Carbon::parse($currentDate)->format('d.m.Y') . " tarihinde hata: " . $conflictError 
                    : $conflictError;

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return back()->with('error', $errorMsg);
            }
        }

        // Kaydet
        foreach ($datesToProcess as $currentDate) {
            EmployeeTimeBlock::create([
                'employee_id' => $employeeId,
                'type' => $type,
                'date' => $currentDate,
                'start_time' => $type === 'partial_time' ? $startTime : null,
                'end_time' => $type === 'partial_time' ? $endTime : null,
                'reason' => $reason,
                'created_by' => auth()->id(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Hızlı işlem başarıyla uygulandı.']);
        }
        return back()->with('success', 'Hızlı işlem başarıyla uygulandı.');
    }

    public function destroy(Request $request, EmployeeTimeBlock $employeeTimeBlock)
    {
        $oldIds = $request->input('grouped_ids') 
            ? explode(',', $request->input('grouped_ids')) 
            : [$employeeTimeBlock->id];

        EmployeeTimeBlock::whereIn('id', $oldIds)->delete();

        return back()->with('success', 'Kısıtlama başarıyla kaldırıldı.');
    }
}
