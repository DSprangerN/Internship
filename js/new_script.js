
// Unified script with multilingual support

// Messages in Portuguese
const messages_pt = {

};

// Messages in English
const messages_en = {

};

// Function to get the messages based on the language
function getMessages() {
    const lang = document.documentElement.lang;
    if (lang === 'pt') {
        return messages_pt;
    } else {
        return messages_en;
    }
}

const messages = getMessages();

// Rest of the script logic
// Função para abrir o menu de navegação
function openNav() {
    const topNav = document.getElementById("myTopnav");
    const main = document.getElementById("main");
    if (topNav) topNav.style.height = "80px";
    if (main) main.style.marginTop = "80px";
}

// Função para fechar o menu de navegação
function closeNav() {
    const topNav = document.getElementById("myTopnav");
    const main = document.getElementById("main");
    if (topNav) topNav.style.height = "0";
    if (main) main.style.marginTop = "0";
}

// Função para criar uma caixa de confirmação personalizada
function confirmCustom(message, yesText, noText) {
    return new Promise((resolve) => {
        let confirmBox = document.createElement("div");
        confirmBox.className = "confirm-box";
        confirmBox.innerHTML = `
            <div class="confirm-message">
                <img src="/Internship/img/mascote.png" alt="Mascote" class="mascote-confirm">
                <p>${message}</p>
            </div>
            <button class="confirm-yes">${yesText}</button>
            <button class="confirm-no">${noText}</button>
        `;
        document.body.appendChild(confirmBox);

        // Botão "Sim" resolve a promessa com true
        confirmBox.querySelector(".confirm-yes").onclick = () => {
            resolve(true);
            document.body.removeChild(confirmBox);
        };

        // Botão "Não" resolve a promessa com false
        confirmBox.querySelector(".confirm-no").onclick = () => {
            resolve(false);
            document.body.removeChild(confirmBox);
        };
    });
}

// Função para iniciar a ajuda interativa
function startHelp() {
    // Lista de elementos que a mascote deve destacar
    const elements = [
        { selector: "#btn-menu", message: "Este botão abre o menu." },
        { selector: "#btn-pt", message: "Este botão muda o idioma para Português." },
        { selector: "#btn-eng", message: "Este botão muda o idioma para Inglês." },
        { selector: "#video_escola", message: "Aqui você pode assistir ao vídeo da escola." },
        { selector: "#galeria", message: "Aqui pode ver uma galeria com fotos" },
        { selector: "#missao", message: "Aqui pode ver qual é a nossa missão enquanto Jardim de Infância" },
        { selector: "#ementa", message: "Aqui pode consultar a ementa mensal" },
        { selector: "#curriculares", message: "Aqui pode ver as atividades curriculares que temos na Estrelinha Amarela" },
        { selector: "#extracurriculares", message: "Aqui pode ver que atividades extracurriculares que temos disponíveis" },
        { selector: "#inscricao", message: "Aqui pode fazer a inscrição do seu educando" },
        { selector: "#contactos", message: "Aqui pode consultar os contactos, morada e obter as direções para a Estrelinha Amarela" },
        { selector: ".reviews", message: "Aqui pode deixar um comentário da sua experiência connosco." },
        { selector: ".review-list", message: "Aqui podes consultar experiências passadas de outros encarregados de educação que tiveram connosco" }
        // Adicione novos elementos aqui, seguindo o formato { id: "id-do-elemento", message: "Mensagem da mascote." }
    ];

    let indice = 0; // Índice para rastrear o elemento atual
    const mascote = document.getElementById("mascote");
    const mascoteMessage = document.getElementById("mascote-message");

    if (!mascote || !mascoteMessage) return; // Se a mascote ou a mensagem não existirem, não faz nada

    function nextElement() {
        if (indice < elements.length) {
            const element = document.querySelector(elements[indice].selector);
            if (!element) {
                // Se o elemento não existir, pula para o próximo
                indice++;
                nextElement();
                return;
            }

            element.scrollIntoView({ behavior: "smooth" }); // Rola até o elemento

            // Posiciona a mascote ao lado ou abaixo do elemento destacado
            const rect = element.getBoundingClientRect();
            const windowWidth = window.innerWidth;

            if (rect.right + 150 > windowWidth) {
                mascote.style.top = rect.bottom + window.scrollY + 10 + "px";
                mascote.style.left = Math.min(rect.left + window.scrollX, windowWidth - 150) + "px";
                mascoteMessage.style.top = rect.bottom + window.scrollY + 120 + "px";
                mascoteMessage.style.left = Math.min(rect.left + window.scrollX, windowWidth - 300) + "px";
            } else {
                mascote.style.top = rect.top + window.scrollY + "px";
                mascote.style.left = rect.right + 10 + window.scrollX + "px";
                mascoteMessage.style.top = rect.top + window.scrollY + "px";
                mascoteMessage.style.left = rect.right + 120 + window.scrollX + "px";
            }

            mascoteMessage.innerText = elements[indice].message; // Define a mensagem da mascote
            indice++;
        } else {
            // Finaliza a interação
            mascote.style.display = "none";
            mascoteMessage.style.display = "none";
        }
    }

    // Adiciona o evento de clique na mascote para avançar para o próximo elemento
    mascote.addEventListener("click", nextElement);

    // Inicia a interação com o primeiro elemento
    nextElement();
}

// Função de Ajuda interativa
function Help() {
    confirmCustom("Queres que te mostre o meu site?", "Sim quero a tua ajuda", "Agora não, obrigado").then((wantHelp) => {
        const mascote = document.getElementById("mascote");
        const mascoteMessage = document.getElementById("mascote-message");

        if (wantHelp && mascote && mascoteMessage) {
            mascote.style.display = "block";
            mascoteMessage.style.display = "block";
            startHelp();
        }
    });
}

// Exibe a caixa de texto ao carregar a página
// Exibe a caixa de texto ao carregar a página
window.addEventListener("load", function () {
    Help(); //função de ajuda interativa
    if (typeof loadReviews === "function") {
        loadReviews(); //Só chama esta função se existir (contacts.html) assim evita erros em outras páginas
    }
});
