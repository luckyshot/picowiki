<!DOCTYPE html>
<html lang="en" data-bs-theme="<?=$this->config['theme']?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$this->url?''.ucwords(str_replace(['-','/'],' ',$this->url)).' · ':''?><?=$this->config['app_name']?></title>
    <link rel="stylesheet" href="<?=$this->config['app_url']?>static/<?=$this->config['theme']?>.css" id="theme-link">
    <link rel="stylesheet" href="<?=$this->config['app_url']?>static/default.css">
    <link rel="shortcut icon" href="<?=$this->config['app_url']?>static/picowiki-favicon.png" type="image/png">
	<?=$this->event('template_header', $this)?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer"> -->



<Script>
    const storedTheme = localStorage.getItem('theme')

    const getPreferredTheme = () => {
        if (storedTheme) {
            return storedTheme
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    const setTheme = function(theme) {
        if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark')

        } else {
            document.documentElement.setAttribute('data-bs-theme', theme)
 
        }
    }

    setTheme(getPreferredTheme())

    const showActiveTheme = (theme, focus = false) => {
        const themeSwitcher = document.querySelector('#bd-theme')

        if (!themeSwitcher) {
            return
        }

        const themeSwitcherText = document.querySelector('#bd-theme-text')
        const activeThemeIcon = document.querySelector('.theme-icon-active use')
        const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
        const svgOfActiveBtn = btnToActive.querySelector('svg use').getAttribute('href')

        document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
            element.classList.remove('active')
            element.setAttribute('aria-pressed', 'false')
        })

        btnToActive.classList.add('active')
        btnToActive.setAttribute('aria-pressed', 'true')
        activeThemeIcon.setAttribute('href', svgOfActiveBtn)
        const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActive.dataset.bsThemeValue})`
        themeSwitcher.setAttribute('aria-label', themeSwitcherLabel)

        if (focus) {
            themeSwitcher.focus()
        }
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (storedTheme !== 'light' || storedTheme !== 'dark') {
            setTheme(getPreferredTheme())
        }
    })

    window.addEventListener('DOMContentLoaded', () => {
        showActiveTheme(getPreferredTheme())

        document.querySelectorAll('[data-bs-theme-value]')
            .forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const theme = toggle.getAttribute('data-bs-theme-value')
                    localStorage.setItem('theme', theme)
                    setTheme(theme)
                    showActiveTheme(theme, true)
                })
            })
    })

    function toggleTheme() {
        const storedTheme = localStorage.getItem('theme')
        const newTheme = storedTheme === 'light' ? 'dark' : 'light'

        localStorage.setItem('theme', newTheme)
        setTheme(newTheme)
        showActiveTheme(newTheme)

        const themeIcon = document.querySelector('#theme-icon')
        const themeText = document.querySelector('#theme-text')
        if (storedTheme === 'light') {
            themeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M361.5 1.2c5 2.1 8.6 6.6 9.6 11.9L391 121l107.9 19.8c5.3 1 9.8 4.6 11.9 9.6s1.5 10.7-1.6 15.2L446.9 256l62.3 90.3c3.1 4.5 3.7 10.2 1.6 15.2s-6.6 8.6-11.9 9.6L391 391 371.1 498.9c-1 5.3-4.6 9.8-9.6 11.9s-10.7 1.5-15.2-1.6L256 446.9l-90.3 62.3c-4.5 3.1-10.2 3.7-15.2 1.6s-8.6-6.6-9.6-11.9L121 391 13.1 371.1c-5.3-1-9.8-4.6-11.9-9.6s-1.5-10.7 1.6-15.2L65.1 256 2.8 165.7c-3.1-4.5-3.7-10.2-1.6-15.2s6.6-8.6 11.9-9.6L121 121 140.9 13.1c1-5.3 4.6-9.8 9.6-11.9s10.7-1.5 15.2 1.6L256 65.1 346.3 2.8c4.5-3.1 10.2-3.7 15.2-1.6zM160 256a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zm224 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0z"/></svg>'

                        // remove the theme-link tag, if it exists

                        const themeLink = document.querySelector('#theme-link')
            themeLink.parentNode.removeChild(themeLink)
            // add the new theme-link tag
            const newThemeLink = document.createElement('link')
            newThemeLink.setAttribute('rel', 'stylesheet')
            newThemeLink.setAttribute('href', '<?=$this->config['app_url']?>static/dark.css')
            newThemeLink.setAttribute('id', 'theme-link')
            document.head.appendChild(newThemeLink)

            const prismLink = document.querySelector('#prism-link')
            prismLink.parentNode.removeChild(prismLink)
            // add the new theme-link tag
            const newPrismLink = document.createElement('link')
            newPrismLink.setAttribute('rel', 'stylesheet')
            newPrismLink.setAttribute('href', '<?=$this->config['app_url']?>static/prism-dark.css')
            newPrismLink.setAttribute('id', 'prism-link')
            document.head.appendChild(newPrismLink)


            // themeText.textContent = 'Dark Mode'
        } else {
            // themeText.textContent = 'Light Mode'
            themeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M223.5 32C100 32 0 132.3 0 256S100 480 223.5 480c60.6 0 115.5-24.2 155.8-63.4c5-4.9 6.3-12.5 3.1-18.7s-10.1-9.7-17-8.5c-9.8 1.7-19.8 2.6-30.1 2.6c-96.9 0-175.5-78.8-175.5-176c0-65.8 36-123.1 89.3-153.3c6.1-3.5 9.2-10.5 7.7-17.3s-7.3-11.9-14.3-12.5c-6.3-.5-12.6-.8-19-.8z"/></svg>'



                        // remove the theme-link tag
                        const themeLink = document.querySelector('#theme-link')
            themeLink.parentNode.removeChild(themeLink)
            // add the new theme-link tag
            const newThemeLink = document.createElement('link')
            newThemeLink.setAttribute('rel', 'stylesheet')
            newThemeLink.setAttribute('href', `<?=$this->config['app_url']?>static/light.css`)
            newThemeLink.setAttribute('id', 'theme-link')
            document.head.appendChild(newThemeLink)

            const prismLink = document.querySelector('#prism-link')
            prismLink.parentNode.removeChild(prismLink)
            // add the new theme-link tag
            const newPrismLink = document.createElement('link')
            newPrismLink.setAttribute('rel', 'stylesheet')
            newPrismLink.setAttribute('href', '<?=$this->config['app_url']?>static/prism-light.css')
            newPrismLink.setAttribute('id', 'prism-link')
            document.head.appendChild(newPrismLink)
        }
    }

    //when dom is loaded, check if theme is dark or light and change the icon accordingly
    document.addEventListener('DOMContentLoaded', function() {
        const storedTheme = localStorage.getItem('theme')
        const themeIcon = document.querySelector('#theme-icon')
        const themeText = document.querySelector('#theme-text')
        if (storedTheme === 'light') {
            themeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M223.5 32C100 32 0 132.3 0 256S100 480 223.5 480c60.6 0 115.5-24.2 155.8-63.4c5-4.9 6.3-12.5 3.1-18.7s-10.1-9.7-17-8.5c-9.8 1.7-19.8 2.6-30.1 2.6c-96.9 0-175.5-78.8-175.5-176c0-65.8 36-123.1 89.3-153.3c6.1-3.5 9.2-10.5 7.7-17.3s-7.3-11.9-14.3-12.5c-6.3-.5-12.6-.8-19-.8z"/></svg>'


            // remove the theme-link tag
            const themeLink = document.querySelector('#theme-link')
            themeLink.parentNode.removeChild(themeLink)
            // add the new theme-link tag
            const newThemeLink = document.createElement('link')
            newThemeLink.setAttribute('rel', 'stylesheet')
            newThemeLink.setAttribute('href', `<?=$this->config['app_url']?>static/light.css`)
            newThemeLink.setAttribute('id', 'theme-link')
            document.head.appendChild(newThemeLink)

            const prismLink = document.querySelector('#prism-link')
            prismLink.parentNode.removeChild(prismLink)
            // add the new theme-link tag
            const newPrismLink = document.createElement('link')
            newPrismLink.setAttribute('rel', 'stylesheet')
            newPrismLink.setAttribute('href', '<?=$this->config['app_url']?>static/prism-light.css')
            newPrismLink.setAttribute('id', 'prism-link')
            document.head.appendChild(newPrismLink)

            // themeText.textContent = 'Dark Mode'
        } else {
            themeIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M361.5 1.2c5 2.1 8.6 6.6 9.6 11.9L391 121l107.9 19.8c5.3 1 9.8 4.6 11.9 9.6s1.5 10.7-1.6 15.2L446.9 256l62.3 90.3c3.1 4.5 3.7 10.2 1.6 15.2s-6.6 8.6-11.9 9.6L391 391 371.1 498.9c-1 5.3-4.6 9.8-9.6 11.9s-10.7 1.5-15.2-1.6L256 446.9l-90.3 62.3c-4.5 3.1-10.2 3.7-15.2 1.6s-8.6-6.6-9.6-11.9L121 391 13.1 371.1c-5.3-1-9.8-4.6-11.9-9.6s-1.5-10.7 1.6-15.2L65.1 256 2.8 165.7c-3.1-4.5-3.7-10.2-1.6-15.2s6.6-8.6 11.9-9.6L121 121 140.9 13.1c1-5.3 4.6-9.8 9.6-11.9s10.7-1.5 15.2 1.6L256 65.1 346.3 2.8c4.5-3.1 10.2-3.7 15.2-1.6zM160 256a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zm224 0a128 128 0 1 0 -256 0 128 128 0 1 0 256 0z"/></svg>'

            // themeText.textContent = 'Light Mode'

            // remove the theme-link tag, if it exists

            const themeLink = document.querySelector('#theme-link')
            themeLink.parentNode.removeChild(themeLink)
            // add the new theme-link tag
            const newThemeLink = document.createElement('link')
            newThemeLink.setAttribute('rel', 'stylesheet')
            newThemeLink.setAttribute('href', '<?=$this->config['app_url']?>static/dark.css')
            newThemeLink.setAttribute('id', 'theme-link')
            document.head.appendChild(newThemeLink)

            const prismLink = document.querySelector('#prism-link')
            prismLink.parentNode.removeChild(prismLink)
            // add the new theme-link tag
            const newPrismLink = document.createElement('link')
            newPrismLink.setAttribute('rel', 'stylesheet')
            newPrismLink.setAttribute('href', '<?=$this->config['app_url']?>static/prism-dark.css')
            newPrismLink.setAttribute('id', 'prism-link')
            document.head.appendChild(newPrismLink)

        }
    })
</script>

</head>
<body>

<div id="main" class="main">
