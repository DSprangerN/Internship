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
        { selector: ".btn-menu", message: "Here you can open the menu." },
        { selector: "#btn-pt", message: "Changes the language to Portuguese." },
        { selector: "#btn-eng", message: "Changes the language to English." },
        { selector: "#video_escola", message: "Here you can watch a video of the Kindergarten." },
        { selector: "#galleryImg", message: "Here you can see a photo gallery of the Kindergarten." },
        { selector: "#contact-info", message: "Here you can find the contacts of the Kindergarten." },
        { selector: "#ementa", message: "Here you can see the monthly menu." },
        { selector: "#missao", message: "Here we have what is our mission as a Kindergarten" },
        { selector: "#mapa", message: "Here you can see the location of the Kindergarten on the map and get the directions" },
        { selector: "#reviews", message: "Here you can leave a review about your experience with the Kindergarten" },
        { selector: "review-list", message: "Here you can see the reviews left by other parents." },
        { selector: "#curriculares", message: "Here you can see the activities we have included at the Kindergarten." },
        { selector: "#extracurriculares", message: "Here you can see the extra activities we have available at the Kindergarten" },
        { selector: "#inscricao", message: "Here you can submit your aplication." },
        { selector: "#contactos", message: "At this section you can see the contacts information of the Estrelinha Amarela Kindergarten." },
        { selector: ".reviews", message: "In this section you can leave a review of your experience at the Kindergarten." },
        { selector: ".review-list", message: "Here you can see all the reviews left from different parents." }
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
    confirmCustom("Would you like me to show you the Website?", "Yes please", "Not now, thank you").then((wantHelp) => {
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
window.addEventListener("load", function () {
    Help(); //função de ajuda interativa
    if (typeof loadReviews === "function") {
        loadReviews(); //Só chama esta função se existir (contacts.html) assim evita erros em outras páginas
    }
});