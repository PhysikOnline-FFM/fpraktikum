function confirmAbmeldung() {
    if (!confirm('Sicher, dass du deine Anmeldung zurückziehen willst?')) {
        window.location.href = "./";
    }
}