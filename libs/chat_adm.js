// Função para carregar mensagens
function carregarMensagens() {
    $.ajax({
        url: 'carregar_mensagens.php?t=' + new Date().getTime(), // Evita cache
        method: 'GET',
        dataType: 'json', // Especifica que a resposta é JSON
        success: function(response) {
            if (response.status === 'success') {
                let html = '';

                response.messages.forEach(msg => {
                    // Aplica uma classe diferente se for admin
                    let classeMensagem = "admin-message";

                    // Substitui quebras de linha por <br> para exibir corretamente no HTML
                    let mensagemFormatada = msg.message.replace(/\n/g, '<br>');

                    html += `<p class="${classeMensagem}"><strong>${msg.name}</strong> (${msg.sent_in}):<br>${mensagemFormatada}</p>`;
                });

                $('#chat').html(html);
                $('#chat').scrollTop($('#chat')[0].scrollHeight);
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            alert("Erro ao carregar as mensagens: " + error);
        }
    });
}

// Função para enviar mensagens
function enviarMensagem() {
    const mensagem = $('#mensagem').val();
    if (mensagem.trim() !== '') {
        $.ajax({
            url: 'enviar_mensagem.php',
            method: 'POST',
            data: { mensagem: mensagem },
            dataType: 'json', // Especifica que a resposta é JSON
            timeout: 5000, // Timeout de 5 segundos
            success: function(response) {
                console.log(response); // Exibe a resposta no console para depuração
                // A resposta já é um objeto JavaScript, não é necessário usar JSON.parse
                if (response.status === 'success') {
                    $('#mensagem').val(''); // Limpa o campo de mensagem
                    console.log("Input limpo.");
                    carregarMensagens(); // Recarrega as mensagens
                } else {
                    alert(response.message); // Exibe uma mensagem de erro
                }
            },
            error: function(xhr, status, error) {
                if (status === "timeout") {
                    alert("A requisição demorou muito. Verifique sua conexão.");
                } else {
                    alert("Erro ao enviar a mensagem: " + error);
                }
            }
        });
    } else {
        alert("A mensagem não pode estar vazia.");
    }
}

// Atualiza as mensagens a cada 2 segundos
setInterval(carregarMensagens, 2000);
carregarMensagens(); // Carrega as mensagens ao abrir a página