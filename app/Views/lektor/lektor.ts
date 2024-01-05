window.addEventListener('scroll', function () {
    const header = document.getElementById('main-header');
    const p = document.querySelector('p');
    const darkModeIcon = document.getElementById('logo') as HTMLImageElement; // Přidáno pro ikonu dark mode
    const isEnabled = document.body.classList.contains('dark-mode');
    const scrollPos = window.scrollY;

    if (header == null || p == null || darkModeIcon == null) {
        return;
    }
    // Header shrink
    if (scrollPos > 50) {
        header.classList.add('shrink');
        p.style.display = 'none';

        if (isEnabled) {
            darkModeIcon.src = '/icon/TdA_sipka/png/TdA_sipka_bila.png';
        } else {
            darkModeIcon.src = '/icon/TdA_sipka/png/TdA_sipka_cerna.png';
        }
    } else {
        header.classList.remove('shrink');
        p.style.display = 'block';

        if (isEnabled) {
            darkModeIcon.src = '/icon/TdA_LOGO/TeacherDigitalAgency_LOGO_white.png';
        } else {
            darkModeIcon.src = '/icon/TdA_LOGO/TeacherDigitalAgency_LOGO_black.png';
        }
    }
});

// Show more tags
function showMoreTags() {
    const tags = document.getElementById('tags');
    const showTags = document.getElementById('showTags');
    const hideTags = document.getElementById('hideTags');
    if (tags == null || showTags == null || hideTags == null) {
        return;
    }

    const numOfTags = tags.childElementCount;

    if (numOfTags - 2 <= 3) {
        showTags.style.display = 'none';
        hideTags.style.display = 'none';
    } else {
        //Show first 3 tags
        for (let i = 0; i < numOfTags; i++) {
            const child = tags.children[i] as HTMLElement;
            if (i < 3) {
                child.style.display = 'inline-block';
            } else {
                child.style.display = 'none';
            }
        }
        showTags.style.display = 'inline-block';

        // Show more tags
        showTags.addEventListener('click', function () {
                for (let i = 0; i < numOfTags; i++) {
                    if (i > 2) {
                        (tags.children[i] as HTMLElement).style.display = 'inline-block';
                    }
                }
                showTags.style.display = 'none';
            }
        );

        // Hide more tags
        hideTags.addEventListener('click', function () {
                for (let i = 0; i < numOfTags; i++) {
                    if (i > 2) {
                        (tags.children[i] as HTMLElement).style.display = 'none';
                    }
                }
                showTags.style.display = 'inline-block';
            }
        );
    }

}

// Event listener for show more tags
showMoreTags();

// Dark mode function
function toggleDarkMode() {
    const body = document.body;
    const isEnabled = body.classList.toggle('dark-mode');

    // Set right color for dark mode icon
    const darkModeIcon = document.getElementById('darkModeIcon') as HTMLImageElement
    if (darkModeIcon == null) {
        return;
    }

    if (isEnabled) {
        darkModeIcon.src = '/icon/TdA_ikony/png/TdA_ikony_nastaveni_white.png';
    } else {
        darkModeIcon.src = '/icon/TdA_ikony/png/TdA_ikony_nastaveni_black.png';
    }

    const logo = document.querySelector('header img') as HTMLImageElement | null;
    const scrollPos = window.scrollY;
    if (logo == null) {
        return;
    }
    // Update logo for dark mode
    if (isEnabled) {
        if (scrollPos > 50) {
            logo.src = '/icon/TdA_sipka/png/TdA_sipka_bila.png';
        } else {
            logo.src = '/icon/TdA_LOGO/TeacherDigitalAgency_LOGO_white.png';
        }
    } else {
        if (scrollPos > 50) {
            logo.src = '/icon/TdA_sipka/png/TdA_sipka_cerna.png';
        } else {
            logo.src = '/icon/TdA_LOGO/TeacherDigitalAgency_LOGO_black.png';
        }
    }

    // Local storage
    localStorage.setItem('dark-mode', isEnabled ? 'enabled' : 'disabled');
}

toggleDarkMode();


// Return image for dark mode
function handleDarkModeActive() {
    const darkModeIcon = document.getElementById('darkModeIcon') as HTMLImageElement | null;
    //const darkModeText = document.getElementById('darkModeText');
    const isEnabled = document.body.classList.contains('dark-mode');
    if (darkModeIcon == null) {
        return;
    }

    // Set right color for dark mode icon
    if (isEnabled) {
        darkModeIcon.src = '/icon/TdA_ikony/png/TdA_ikony_nastaveni_white.png';
    } else {
        darkModeIcon.src = '/icon/TdA_ikony/png/TdA_ikony_nastaveni_black.png';
    }
}

// Event listener for dark mode toggle
const darkModeToggle = document.getElementById('darkModeToggle');
if (darkModeToggle !== null) {
    darkModeToggle.addEventListener('click', handleDarkModeActive);
}