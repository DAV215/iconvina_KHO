var modal = document.getElementById('modalReceipt');

function closeModal(idModal) {
    var modal = document.getElementById(idModal);
    modal.style.display = "none";
}

function openModal(idModal) {
    var modal = document.getElementById(idModal);
    modal.style.display = "flex";
}