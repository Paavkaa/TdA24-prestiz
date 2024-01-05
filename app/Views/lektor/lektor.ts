window.addEventListener('scroll', function () 
{
    const header = document.getElementById('main-header');
    const p = document.querySelector('p');
    const darkModeIcon = document.getElementById('logo'); // Přidáno pro ikonu dark mode
    const isEnabled = document.body.classList.contains('dark-mode');
    const scrollPos = window.scrollY;

    // Header shrink
    if (scrollPos > 50) 
    {
        header.classList.add('shrink');
        p.style.display = 'none';

        if (isEnabled) 
        {
            darkModeIcon.src = 'icon/TdA_sipka/png/TdA_sipka_bila.png';
        } 
        else 
        {
            darkModeIcon.src = 'icon/TdA_sipka/png/TdA_sipka_cerna.png';
        }
    } 
    else 
    {
        header.classList.remove('shrink');
        p.style.display = 'block';

        if (isEnabled) 
        {
            darkModeIcon.src = 'icon/TdA_LOGO/TeacherDigitalAgency_LOGO_white.png';
        } 
        else 
        {
            darkModeIcon.src = 'icon/TdA_LOGO/TeacherDigitalAgency_LOGO_black.png';
        }
    }
});

    // Show more tags
    function showMoreTags() 
    {
    const tags = document.getElementById('tags');
    const showTags = document.getElementById('showTags');
    const hideTags = document.getElementById('hideTags');
    const numOfTags = tags.childElementCount;

    if (numOfTags-2 <= 3) 
    {
        showTags.style.display = 'none';
        hideTags.style.display = 'none';
    }
    else
    {
        //Show first 3 tags
        for (let i = 0; i < numOfTags; i++) 
        {
            if (i < 3) 
            {
                tags.children[i].style.display = 'inline-block';
            } 
            else 
            {
                tags.children[i].style.display = 'none';
            }
        }
        showTags.style.display = 'inline-block';

        // Show more tags
        showTags.addEventListener('click', function () 
        {
            for (let i = 0; i < numOfTags; i++) 
            {
                if (i > 2) 
                {
                    tags.children[i].style.display = 'inline-block';
                }
            }
            showTags.style.display = 'none';
        }
        );

        // Hide more tags
        hideTags.addEventListener('click', function () 
        {
            for (let i = 0; i < numOfTags; i++) 
            {
                if (i > 2) 
                {
                    tags.children[i].style.display = 'none';
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
    function toggleDarkMode() 
    {
        const body = document.body;
        const isEnabled = body.classList.toggle('dark-mode');

        // Set right color for dark mode icon
        const darkModeIcon = document.getElementById('darkModeIcon');

        if (isEnabled) 
        {
            darkModeIcon.src = 'icon/TdA_ikony/png/TdA_ikony_nastaveni_white.png';
        } 
        else 
        {
            darkModeIcon.src = 'icon/TdA_ikony/png/TdA_ikony_nastaveni_black.png';
        }

        const logo = document.querySelector('header img');
        const scrollPos = window.scrollY;


        // Update logo for dark mode
        if (isEnabled) 
        {
            if (scrollPos > 50) 
            {
                logo.src = 'icon/TdA_sipka/png/TdA_sipka_bila.png';
            } 
            else 
            {
                logo.src = 'icon/TdA_LOGO/TeacherDigitalAgency_LOGO_white.png';
            }
        } 
        else 
        {
            if (scrollPos > 50) 
            {
                logo.src = 'icon/TdA_sipka/png/TdA_sipka_cerna.png';
            } 
            else 
            {
                logo.src = 'icon/TdA_LOGO/TeacherDigitalAgency_LOGO_black.png';
            }
        }

        // Local storage
        localStorage.setItem('dark-mode', isEnabled ? 'enabled' : 'disabled');
    }
    toggleDarkMode();


// Return image for dark mode
function handleDarkModeActive() 
{
    const darkModeIcon = document.getElementById('darkModeIcon');
    //const darkModeText = document.getElementById('darkModeText');
    const isEnabled = document.body.classList.contains('dark-mode');

    // Set right color for dark mode icon
    if (isEnabled) 
    {
        darkModeIcon.src = 'icon/TdA_ikony/png/TdA_ikony_nastaveni_white.png';
    } 
    else 
    {
        darkModeIcon.src = 'icon/TdA_ikony/png/TdA_ikony_nastaveni_black.png';
    }
}

// Event listener for dark mode toggle
const darkModeToggle = document.getElementById('darkModeToggle');
darkModeToggle.addEventListener('click', handleDarkModeActive);