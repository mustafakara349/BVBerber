<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Blade;

class BladeOutputEscapingTest extends TestCase
{
    /**
     * Test that no Blade template contains raw {!! json_encode(...) !!}
     */
    public function test_no_raw_json_encode_in_blade_templates(): void
    {
        $viewsPath = resource_path('views');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsPath));
        $violatingFiles = [];

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $content = file_get_contents($file->getPathname());
                if (preg_match('/\{\!\!.*json_encode.*\!\!\}/s', $content)) {
                    $violatingFiles[] = str_replace($viewsPath, '', $file->getPathname());
                }
            }
        }

        $this->assertEmpty($violatingFiles, 'Raw {!! json_encode(...) !!} found in: ' . implode(', ', $violatingFiles));
    }

    /**
     * Test that @json directive safely encodes HTML and script tag breakout attempts
     */
    public function test_json_directive_escapes_html_and_script_breakout(): void
    {
        $maliciousPayload = [
            'name' => "</script><script>alert('XSS')</script>",
            'quote' => '"><img src=x onerror=alert(1)>',
            'ampersand' => 'test & "value"',
        ];

        $rendered = Blade::render('@json($data)', ['data' => $maliciousPayload]);

        // Must not contain raw script tags or raw unescaped quotes/brackets
        $this->assertStringNotContainsString('</script>', $rendered);
        $this->assertStringNotContainsString('<script>', $rendered);
        $this->assertStringNotContainsString('><img', $rendered);

        // Must contain JSON hex escapes (\u003C for <, \u003E for >, \u0022 for quotes)
        $this->assertStringContainsString('\u003C', $rendered);
        $this->assertStringContainsString('\u003E', $rendered);
    }

    /**
     * Test that critical Blade views include escapeHtml helper and escape dynamic DOM interpolations
     */
    public function test_blade_views_use_html_escaping_for_dom_interpolations(): void
    {
        $campaignsView = file_get_contents(resource_path('views/campaigns/index.blade.php'));
        $this->assertStringContainsString('function escapeHtml', $campaignsView);
        $this->assertStringContainsString('escapeHtml(customerName)', $campaignsView);
        $this->assertStringContainsString('escapeHtml(employeeName)', $campaignsView);
        $this->assertStringContainsString('escapeHtml(appointmentCode)', $campaignsView);

        $salesView = file_get_contents(resource_path('views/products/sales.blade.php'));
        $this->assertStringContainsString('function escapeHtml', $salesView);
        $this->assertStringContainsString('escapeHtml(customer.full_name', $salesView);
        $this->assertStringContainsString('escapeHtml(product.name)', $salesView);
        $this->assertStringContainsString('escapeHtml(seller', $salesView);

        $purchaseOrdersView = file_get_contents(resource_path('views/purchase_orders/create.blade.php'));
        $this->assertStringContainsString('function escapeHtml', $purchaseOrdersView);
        $this->assertStringContainsString('escapeHtml(p.name)', $purchaseOrdersView);
        $this->assertStringContainsString('escapeHtml(e)', $purchaseOrdersView);

        $dashboardView = file_get_contents(resource_path('views/dashboard/index.blade.php'));
        $this->assertStringContainsString('function escapeHtml', $dashboardView);
        $this->assertStringContainsString('escapeHtml(service.name)', $dashboardView);
    }
}
