<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/lektor/lektor.js" defer></script>
    <title>Lektor</title>
</head>


<body>
<img class="background" src="/icon/Background/Wave.svg" alt="" srcset="">

<header id="main-header">

    <img id="logo" src="/icon/TdA_LOGO/TeacherDigitalAgency_LOGO_black.png" alt="TdA_LOGO">

    <button class="dark-mode-toggle" onclick="toggleDarkMode()" id="toggleDarkMode">
        <img id="darkModeIcon" src="/icon/TdA_ikony/png/TdA_ikony_nastaveni_black.png" alt="Dark Mode Toggle">
        <!-- todo Change light/dark -->
        <p id="darkModeText">Režim</p>
    </button>

</header>

<div class="content">

    <div class="heading">

        <img src="<?php echo $data['picture_url'] ?? "" ?>"
             alt="lector_pic">
        <div>

            <h1>
                <?php echo $data['title_before'] ?? "" ?>
                <?php echo $data['first_name'] ?? "" ?>
                <?php echo $data['middle_name'] ?? "" ?>
                <?php echo $data['last_name'] ?? "" ?>
                <?php echo $data['title_before'] ?? "" ?>
            </h1>
            <h2><?php echo $data['claim'] ?? "" ?></h2>

        </div>

    </div>

    <div class="info">

        <?php if (isset($data['tags']) && is_array($data['tags'])): ?>
            <div id="tags">
                <?php foreach ($data['tags'] as $tag): ?>
                    <a class="tag" href="#"><?= htmlspecialchars($tag['name']) ?></a>
                <?php endforeach; ?>
                <button class="tag" id="showTags">+</button>
                <button class="tag" id="hideTags">-</button>
            </div>
        <?php endif; ?>
        <span>Lokalita:</span>
        <p><?php echo $data['location'] ?? "" ?></p> <br>
        <span>Cena za hodinu:</span>
        <p><?php echo $data['price_per_hour'] ?? "" ?> Kč</p> <br>
        <span>Kontakt:</span> <br>
        <table class="contact">
            <tr>
                <td>
                    <p>
                        <?php echo implode(" , ", array_values($data['contact']['telephone_numbers'])) ?? "" ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p>
                        <?php echo implode(" , ", array_values($data['contact']['emails'])) ?? "" ?>
                    </p>
                </td>
            </tr>
        </table>
        <br>

        <span>O mně:</span> <br>
        <p>
            <?php echo $data['bio'] ?? "" ?>
        </p>
    </div>

</div>


<script>

    /* window.addEventListener('scroll', function ()
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
        <!-- todo Change light/dark text-->
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
    /*const darkModeToggle = document.getElementById('darkModeToggle');
    darkModeToggle.addEventListener('click', handleDarkModeActive); */


</script>

</body>
</html>
