(function() {
    const adUrl = 'https://www.effectivecpmnetwork.com/pz6q346f?key=1f27360cd248a6accacce8e78256083f';
    const cookieName = 'pop_status';
    const expireSeconds = 15; // Durasi diubah menjadi 15 detik

    function setAdCookie(name, value, seconds) {
        let date = new Date();
        date.setTime(date.getTime() + (seconds * 1000));
        document.cookie = name + "=" + value + ";expires=" + date.toUTCString() + ";path=/";
    }

    function getAdCookie(name) {
        let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }

    function executePop() {
        if (getAdCookie(cookieName)) return;

        const win = window.open(adUrl, '_blank');

        if (win) {
            win.blur();
            window.focus();
            setAdCookie(cookieName, 'true', expireSeconds);
        }
    }

    document.addEventListener('click', function() {
        executePop();
    }, { once: true });
})();