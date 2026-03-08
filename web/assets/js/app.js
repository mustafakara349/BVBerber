const routes = {
    'welcome': 'views/welcome.html',
    'services': 'views/services.html',
    'appointments': 'views/appointments.html',
    'profile': 'views/profile.html',
    'appointment_selection': 'views/appointment_selection.html',
    'appointment_confirmation': 'views/appointment_confirmation.html',
};

// Application state for booking flow
const appState = {
    selectedService: null,
    selectedBarber: null,
    selectedDate: null,
    selectedTime: null
};

document.addEventListener('DOMContentLoaded', () => {
    initRouter();
    
    // Add event listeners to navigation items directly
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const target = link.getAttribute('data-target');
            if (target) {
                // Let the hash change trigger the routing
            }
        });
    });
});

function initRouter() {
    window.addEventListener('hashchange', handleRoute);
    
    // Check initial route
    if (!window.location.hash) {
        window.location.hash = '#welcome';
    } else {
        handleRoute();
    }
}

async function handleRoute() {
    const hash = window.location.hash.substring(1) || 'welcome';
    
    // Handle specific screen view states
    const bottomNav = document.getElementById('bottom-nav');
    if (hash === 'appointment_selection' || hash === 'appointment_confirmation') {
        bottomNav.style.transform = 'translateY(100%)'; // Hide nav
    } else {
        bottomNav.style.transform = 'translateY(0)'; // Show nav
        updateActiveNav(hash);
    }

    const appContent = document.getElementById('app-content');
    
    // Simple fade transition
    appContent.classList.remove('fade-in');
    appContent.classList.add('fade-out');
    
    setTimeout(async () => {
        try {
            const path = routes[hash];
            if (!path) throw new Error('Route not found');

            const response = await fetch(path);
            if (!response.ok) throw new Error(`Failed to load ${path}`);
            
            const html = await response.text();
            appContent.innerHTML = html;
            
            // Re-bind any specific scripts for the loaded view
            initViewScripts(hash);

        } catch (error) {
            console.error(error);
            appContent.innerHTML = `<div class="flex items-center justify-center p-8 text-center h-full">
                <div>
                    <span class="material-symbols-outlined text-6xl text-slate-300 md-8">error_outline</span>
                    <h2 class="text-xl font-bold mt-4">Screen Not Found</h2>
                    <p class="text-slate-500 mt-2">Could not load the requested screen.</p>
                </div>
            </div>`;
        }
        
        appContent.classList.remove('fade-out');
        appContent.classList.add('fade-in');
        
        // Scroll to top on route change
        window.scrollTo(0,0);
        
    }, 200); // Wait for fade out
}

function updateActiveNav(activeHash) {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const target = link.getAttribute('data-target');
        const icon = link.querySelector('.nav-icon');
        const text = link.querySelector('.nav-text');
        
        if (target === activeHash) {
            // Set active styles (Primary Color + Filled Icon)
            icon.classList.remove('text-slate-400', 'dark:text-slate-500');
            icon.classList.add('text-primary', 'filled-icon');
            text.classList.remove('text-slate-400', 'dark:text-slate-500');
            text.classList.add('text-primary');
        } else {
            // Set inactive styles
            icon.classList.remove('text-primary', 'filled-icon');
            icon.classList.add('text-slate-400', 'dark:text-slate-500');
            text.classList.remove('text-primary');
            text.classList.add('text-slate-400', 'dark:text-slate-500');
        }
    });
}

function initViewScripts(viewName) {
    // This function acts as the controller for dynamic behaviors injected from the HTML files
    // Since we are loading via fetch, inline <script> tags won't automatically run.
    
    // Universal back button handler
    const backBtn = document.querySelector('[data-action="back"]');
    if(backBtn) {
        backBtn.addEventListener('click', () => {
            window.history.back();
        });
    }

    if (viewName === 'appointment_selection') {
        initAppointmentSelectionLogic();
    }
}

// Below are the specific script logics migrated from the bottom of HTML files
function initAppointmentSelectionLogic() {
     const dateSection = document.querySelectorAll('section')[0];
     const dateButtons = dateSection ? dateSection.querySelectorAll('.flex.gap-3 > button') : [];

     const staffSection = document.querySelectorAll('section')[1];
     const staffCards = staffSection ? staffSection.querySelectorAll('.flex.items-center.gap-4 > div') : [];

     const timeSection = document.querySelectorAll('section')[2];
     const timeButtons = timeSection ? timeSection.querySelectorAll('.grid.grid-cols-4 > button') : [];

     const summarySpans = document.querySelectorAll('.fixed.bottom-0 .flex.items-center.justify-between.px-2 span');
     const summaryDateSpan = summarySpans[0];
     const summaryStaffSpan = summarySpans[1];

     // We start with default values corresponding to HTML structure 
     appState.selectedDayName = 'Pzt';
     appState.selectedDayNum = '23';
     appState.selectedMonth = 'Ekim';
     appState.selectedStaffName = 'Baran V.';
     appState.selectedTime = '10:00';

     function updateSummary() {
         if (summaryDateSpan) {
             summaryDateSpan.textContent = `${appState.selectedDayNum} ${appState.selectedMonth} ${appState.selectedDayName}, ${appState.selectedTime}`;
         }
         if (summaryStaffSpan) {
             summaryStaffSpan.textContent = `Personel: ${appState.selectedStaffName}`;
         }
     }

     // --- DATE LOGIC ---
     dateButtons.forEach(btn => {
         btn.classList.add('cursor-pointer', 'transition-all');
         btn.addEventListener('click', () => {
             dateButtons.forEach(b => {
                 b.className = 'flex flex-col items-center justify-center min-w-[64px] h-20 rounded-xl bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 cursor-pointer transition-all';
                 const s1 = b.querySelector('span:first-child');
                 if (s1) s1.className = 'text-xs font-bold uppercase text-slate-500';
             });
             btn.className = 'flex flex-col items-center justify-center min-w-[64px] h-20 rounded-xl bg-primary text-background-dark shadow-lg shadow-primary/20 cursor-pointer transition-all';
             const s1 = btn.querySelector('span:first-child');
             if (s1) {
                 s1.className = 'text-xs font-bold uppercase opacity-80';
                 appState.selectedDayName = s1.textContent.trim();
             }
             const s2 = btn.querySelector('span:last-child');
             if (s2) appState.selectedDayNum = s2.textContent.trim();

             updateSummary();
         });
     });

     // --- STAFF LOGIC ---
     const checkHTML = `
         <div class="absolute -bottom-1 -right-1 bg-primary text-background-dark rounded-full p-0.5 border-2 border-background-dark check-icon">
             <span class="material-symbols-outlined text-xs font-bold">check</span>
         </div>
     `;

     staffCards.forEach(card => {
         card.classList.add('cursor-pointer', 'transition-all');

         let circleDiv = card.querySelector('.size-16');
         let relativeWrapper = card.querySelector('.relative');

         if (!relativeWrapper && circleDiv) {
             relativeWrapper = document.createElement('div');
             relativeWrapper.className = 'relative';
             circleDiv.parentNode.insertBefore(relativeWrapper, circleDiv);
             relativeWrapper.appendChild(circleDiv);
         }

         const initialCheck = card.querySelector('.absolute.-bottom-1');
         if (initialCheck) {
             initialCheck.classList.add('check-icon');
         }

         card.addEventListener('click', () => {
             staffCards.forEach(c => {
                 const cCircle = c.querySelector('.size-16');
                 const checkIcon = c.querySelector('.check-icon');

                 if (checkIcon) checkIcon.remove();

                 if (cCircle) {
                     cCircle.classList.remove('border-primary');
                     cCircle.classList.add('border-transparent');
                     if (cCircle.querySelector('img')) {
                         cCircle.classList.add('grayscale', 'opacity-60');
                     }
                 }

                 const cSpan = Array.from(c.children).find(el => el.tagName === 'SPAN');
                 if (cSpan) {
                     cSpan.classList.remove('text-primary', 'font-bold');
                     cSpan.classList.add('text-slate-500', 'font-medium');
                 }
             });

             const w = card.querySelector('.relative');
             const cir = card.querySelector('.size-16');
             const sp = Array.from(card.children).find(el => el.tagName === 'SPAN');

             if (w) w.insertAdjacentHTML('beforeend', checkHTML);

             if (cir) {
                 cir.classList.remove('border-transparent', 'grayscale', 'opacity-60');
                 cir.classList.add('border-primary');
             }
             if (sp) {
                 sp.classList.remove('text-slate-500', 'font-medium');
                 sp.classList.add('text-primary', 'font-bold');
                 appState.selectedStaffName = sp.textContent.trim();
             }

             updateSummary();
         });
     });

     // --- TIME LOGIC ---
     timeButtons.forEach(btn => {
         if (btn.classList.contains('cursor-not-allowed')) return;
         btn.classList.add('cursor-pointer', 'transition-all');

         btn.addEventListener('click', () => {
             timeButtons.forEach(b => {
                 if (b.classList.contains('cursor-not-allowed')) return;
                 b.className = 'py-3 rounded-lg bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-sm font-bold hover:border-primary transition-colors cursor-pointer';
             });

             btn.className = 'py-3 rounded-lg bg-primary text-background-dark border border-primary text-sm font-bold shadow-lg shadow-primary/20 cursor-pointer';

             appState.selectedTime = btn.textContent.trim();
             updateSummary();
         });
     });

     // --- SUBMIT ACTION ---
     const submitBtn = document.querySelector('.fixed.bottom-0 button');
     if (submitBtn) {
         submitBtn.addEventListener('click', () => {
             // Navigate to confirmation page
             window.location.hash = '#appointment_confirmation';
         });
     }
}
