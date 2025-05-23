function showReceipt(imageName) {
    document.getElementById('receiptContainer').innerHTML = 
        `<img src="/uploads/receipts/${imageName}" style="max-width:100%;">`;
    document.getElementById('receiptModal').style.display = 'block';
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    if (event.target == document.getElementById('receiptModal')) {
        document.getElementById('receiptModal').style.display = "none";
    }
}