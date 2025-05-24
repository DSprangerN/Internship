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
                <img src="../../img/mascote.png" alt="Mascote" class="mascote-confirm">
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
        { id: "btn-menu", message: "Este botão abre o menu." },
        { id: "btn-pt", message: "Este botão muda o idioma para Português." },
        { id: "btn-eng", message: "Este botão muda o idioma para Inglês." },
        { id: "main-title", message: "Este é o título principal." },
        { id: "video_escola", message: "Aqui você pode assistir ao vídeo da escola." }
        // Adicione novos elementos aqui, seguindo o formato { id: "id-do-elemento", message: "Mensagem da mascote." }
    ];

    let indice = 0; // Índice para rastrear o elemento atual
    const mascote = document.getElementById("mascote");
    const mascoteMessage = document.getElementById("mascote-message");

    if (!mascote || !mascoteMessage) return; // Se a mascote ou a mensagem não existirem, não faz nada

    function nextElement() {
        if (indice < elements.length) {
            const element = document.getElementById(elements[indice].id);
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
window.onload = function () {
    Help(); // Chama a função de ajuda interativa
    loadReviews(); // Carrega as reviews
};

// Função para carregar as reviews do servidor
async function loadReviews() {
    const reviewsList = document.getElementById("reviews");
    if (!reviewsList) {
        console.error("Elemento 'reviews' não encontrado no DOM.");
        return;
    }

    try {
        const response = await fetch("../php/get_reviews.php");
        if (!response.ok) {
            throw new Error(`Erro na requisição: ${response.statusText}`);
        }
        const reviews = await response.json();

        reviewsList.innerHTML = ""; // Limpa a lista antes de renderizar
        reviews.forEach(review => {
            const li = document.createElement("li");
            li.innerHTML = `<strong>${review.name}:</strong> ${review.message}`;
            reviewsList.appendChild(li);
        });
    } catch (error) {
        console.error("Erro ao carregar as reviews:", error);
    }
}

// Exibe a caixa de texto ao carregar a página
window.onload = function () {
    Help(); // Chama a função de ajuda interativa

    // Carrega as reviews apenas no contactos.html
    if (window.location.pathname.endsWith("contactos.html")) {
        loadReviews(); // Carrega as reviews
    }
};

// Função para guardar uma nova review no servidor
async function saveReview() {
    const nameInput = document.getElementById("name");
    const msgInput = document.getElementById("msg");
    const reviewsList = document.getElementById("reviews");

    if (!nameInput || !msgInput || !reviewsList) {
        console.error("Elementos necessários para salvar a review não encontrados no DOM.");
        return;
    }

    const name = nameInput.value.trim();
    const message = msgInput.value.trim();

    if (!name || !message) {
        alert("Por favor, preencha o nome e o comentário.");
        return;
    }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("message", message);

    try {
        const response = await fetch("../php/save_review.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();
        if (result.error) {
            alert(result.error);
        } else {
            alert(result.success);

            // Limpa os campos de entrada
            nameInput.value = "";
            msgInput.value = "";

            // Atualiza a lista de reviews
            loadReviews();
        }
    } catch (error) {
        console.error("Erro ao guardar a review:", error);
    }
}

// Submete a review ao clicar no botão
const submitButton = document.getElementById("submit-review");
if (submitButton) {
    submitButton.addEventListener("click", saveReview);
} else {
    console.error("Botão 'submit-review' não encontrado no DOM.");
}