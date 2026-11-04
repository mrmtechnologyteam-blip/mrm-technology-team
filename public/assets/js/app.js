'use strict';

/*
|--------------------------------------------------------------------------
| MRM Technology Team
| Main JavaScript File
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {

    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {

        link.addEventListener('click', function (event) {

            const targetId = this.getAttribute('href');

            if (targetId.length > 1) {

                const target = document.querySelector(targetId);

                if (target) {
                    event.preventDefault();

                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }

        });

    });

    console.log('MRM Technology Team initialized successfully.');

});
