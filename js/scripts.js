// Script unificado PT/EN para navegação e mascote

// Mensagens da mascote PT
const elements_pt = [
    { selector: "#btn-menu", message: "Este botão abre o menu." },
    { selector: "#btn-pt", message: "Este botão muda o idioma para Português." },
    { selector: "#btn-eng", message: "Este botão muda o idioma para Inglês." },
    { selector: "#video_escola", message: "Aqui você pode assistir ao vídeo da escola." },
    { selector: "#missao", message: "Aqui pode ver qual é a nossa missão enquanto Jardim de Infância" },
    { selector: "#ementa", message: "Aqui pode consultar a ementa mensal" },
    { selector: "#curriculares", message: "Aqui pode ver as atividades curriculares que temos na Estrelinha Amarela" },
    { selector: "#extracurriculares", message: "Aqui pode ver que atividades extracurriculares que temos disponíveis" },
    { selector: "#galleryImg", message: "Aqui pode ver uma galeria de fotos do Jardim de Infância" },
    { selector: "#familia", message: "Aqui podes fazer o download de imagens para colorir em família" },
    { selector: "#inscricao", message: "Aqui pode fazer a inscrição do seu educando" },
    { selector: "#contactos", message: "Aqui pode consultar os contactos, morada e obter as direções para a Estrelinha Amarela" },
    { selector: ".reviews", message: "Aqui pode deixar um comentário sobre a tua experiência connosco" },
    { selector: ".review-list", message: "Aqui podes consultar experiências passadas de outros encarregados de educação que tiveram connosco" },
    { selector: "#docentes", message: "Aqui pode ver os docentes que fazem parte da nossa equipa" },
];

// Mascote EN messages
const elements_en = [
    { selector: ".btn-menu", message: "Here you can open the menu." },
    { selector: "#btn-pt", message: "Changes the language to Portuguese." },
    { selector: "#btn-eng", message: "Changes the language to English." },
    { selector: "#video_escola", message: "Here you can watch a video of the Kindergarten." },
    { selector: "#missao", message: "Here we have what is our mission as a Kindergarten" },
    { selector: "#ementa", message: "Here you can see the monthly menu." },
    { selector: "#curriculares", message: "Here you can see the activities we have included at the Kindergarten." },
    { selector: "#extracurriculares", message: "Here you can see the extra activities we have available at the Kindergarten" },
    { selector: "#galleryImg", message: "Here you can see a photo gallery of the Kindergarten." },
    { selector: "#familia", message: "Here you can download images to color with your family." },
    { selector: "#inscricao", message: "Here you can submit your aplication." },
    { selector: "#contactos", message: "At this section you can see the contacts, location and get the directions of the Estrelinha Amarela Kindergarten." },
    { selector: ".reviews", message: "In this section you can leave a review of your experience at the Kindergarten." },
    { selector: ".review-list", message: "Here you can see all the reviews left from different parents." },
    { selector: "#staff", message: "Here you can see the staff that is part of our team." }
];

// Função para detetar o idioma
function getLang() {
    return document.documentElement.lang === "pt" ? "pt" : "en";
}

// Função para abrir o menu de navegação
function openNav() {
    const topNav = document.getElementById("myTopnav");
    if (topNav) topNav.classList.add("open");
}

// Função para fechar o menu de navegação
function closeNav() {
    const topNav = document.getElementById("myTopnav");
    if (topNav) topNav.classList.remove("open");
}

// Função para criar uma caixa de confirmação personalizada
function confirmCustom(message, yesText, noText) {
    return new Promise((resolve) => {
        let confirmBox = document.createElement("div");
        confirmBox.className = "confirm-box";
        confirmBox.innerHTML = `
        <div class="confirm-message">
            <img src="${getMascotePath()}" alt="Mascote" class="mascote-confirm">
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

function getMascotePath() {
    // Calcula quantos níveis de subpasta existem
    const path = window.location.pathname;
    const parts = path.split('/').filter(Boolean); // remove vazios
    // Encontra a posição da pasta 'img'
    const imgIndex = parts.indexOf('img');
    if (imgIndex !== -1) {
        // Se já está na pasta img, caminho direto
        return 'mascote.png';
    }
    // Calcula prefixo relativo
    let prefix = '';
    // Se estiver em HTML/PT ou HTML/EN, precisa de voltar 2 níveis
    if (parts.includes('PT') || parts.includes('EN')) {
        prefix = '../../img/mascote.png';
    } else if (parts.includes('HTML')) {
        prefix = '../img/mascote.png';
    } else {
        prefix = 'img/mascote.png';
    }
    return prefix;
}

// Função para iniciar a ajuda interativa
function startHelp() {
    const lang = getLang();
    const elements = lang === "pt" ? elements_pt : elements_en;

    let indice = 0;
    const mascote = document.getElementById("mascote");
    const mascoteMessage = document.getElementById("mascote-message");

    if (!mascote || !mascoteMessage) return;

    function nextElement() {
        if (indice < elements.length) {
            const element = document.querySelector(elements[indice].selector);
            if (!element) {
                indice++;
                nextElement();
                return;
            }

            element.scrollIntoView({ behavior: "smooth" });

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

            mascoteMessage.innerText = elements[indice].message;
            indice++;
        } else {
            mascote.style.display = "none";
            mascoteMessage.style.display = "none";
        }
    }

    mascote.onclick = nextElement;
    nextElement();
}

// Função de Ajuda interativa
function Help() {
    const lang = getLang();
    const msg = lang === "pt"
        ? { question: "Queres que te mostre o meu site?", yes: "Sim quero a tua ajuda", no: "Agora não, obrigado" }
        : { question: "Would you like me to show you the Website?", yes: "Yes please", no: "Not now, thank you" };

    confirmCustom(msg.question, msg.yes, msg.no).then((wantHelp) => {
        const mascote = document.getElementById("mascote");
        const mascoteMessage = document.getElementById("mascote-message");

        if (wantHelp && mascote && mascoteMessage) {
            mascote.style.display = "block";
            mascoteMessage.style.display = "block";
            startHelp();
        }
    });
}

// Ao carregar a página
window.addEventListener("load", function () {
    Help();
    if (typeof loadReviews === "function") {
        loadReviews();
    }
});