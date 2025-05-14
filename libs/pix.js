document.getElementById("generate-pix").addEventListener("click", function() {
    const amount = document.getElementById("amount").value;
    if (!amount || amount <= 0) {
        alert("Informe um valor válido.");
        return;
    }

    fetch(`genPix.php?amount=${encodeURIComponent(amount)}`)
        .then(response => response.json())
        .then(data => {
            if (data.codePix && data.qrcode) {
                document.getElementById("codepix").textContent = data.codePix;
                document.getElementById("qrcode").src = data.qrcode;
                document.getElementById("pix-result").style.display = "block";
            } else {
                alert("Erro ao gerar o Pix. Tente novamente.");
            }
        })
        .catch(error => {
            console.error("Erro na requisição Pix:", error);
            alert("Erro ao gerar Pix.");
        });
});

function copiarPix() {
    const codepix = document.getElementById("codepix").innerText;
    navigator.clipboard.writeText(codepix).then(() => {
        alert("Chave Pix copiada com sucesso!");
    }).catch(err => {
        alert("Erro ao copiar: " + err);
    });
}