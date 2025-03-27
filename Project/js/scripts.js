// Função para abrir o menu de forma a escolher as várias seções: Atividades, Missão, Inscrição e Contactos
function openNav() {
    document.getElementById("myTopnav").style.height = "80px";
    document.getElementById("main").style.marginTop = "80px";
}

function closeNav() {
    document.getElementById("myTopnav").style.height = "0";
    document.getElementById("main").style.marginTop = "0";
}

// Função de Ajuda interativa
function Help() {
    let wantHelp = confirmCustom("Queres que te mostre o meu site?", "Sim quero a tua ajuda", "Agora não, obrigado");
    localStorage.setItem("wantHelp", wantHelp);
    if (wantHelp) {
        document.getElementById("mascote").style.display = "block";
        document.getElementById("mascote-message").style.display = "block";
        startHelp();
    }
}

// Função para criar uma caixa de confirmação personalizada
function confirmCustom(message, yesText, noText) {
    return new Promise((resolve) => {
        let confirmBox = document.createElement("div");
        confirmBox.className = "confirm-box";
        confirmBox.innerHTML = `
            <div class="confirm-message">
                <img src="img/mascote.png" alt="Mascote" class="mascote-confirm"> //nova alteração
                ${message}
            </div>
            <button class="confirm-yes">${yesText}</button>
            <button class="confirm-no">${noText}</button>
        `;
        document.body.appendChild(confirmBox);

        // Quando o botão "Sim" é clicado
        confirmBox.querySelector(".confirm-yes").onclick = () => {
            resolve(true);
            document.body.removeChild(confirmBox);
        };

        // Quando o botão "Não" é clicado
        confirmBox.querySelector(".confirm-no").onclick = () => {
            resolve(false);
            document.body.removeChild(confirmBox);
        };
    });
}

// Função para iniciar a ajuda interativa
function startHelp() {
    let elements = [
        { id: "btn-menu", message: "Este botão abre o menu." }, //nova alteração
        { id: "btn-pt", message: "Este botão muda o idioma para Português." }, //nova alteração
        { id: "btn-eng", message: "Este botão muda o idioma para Inglês." }, //nova alteração
        { id: "main-title", message: "Este é o título principal." }, //nova alteração
        { id: "video_escola", message: "Aqui você pode assistir ao vídeo da escola." } //nova alteração
    ];
    let indice = 0;
    let mascote = document.getElementById("mascote");
    let mascoteMessage = document.getElementById("mascote-message");

    function nextElement() {
        if (indice < elements.length) {
            let element = document.getElementById(elements[indice].id);
            element.scrollIntoView({ behavior: "smooth" });
            mascote.style.top = element.offsetTop + "px";
            mascote.style.left = element.offsetLeft + "px";
            mascoteMessage.innerText = elements[indice].message;
            document.querySelectorAll("body > *").forEach(el => el.classList.add("blur"));
            element.classList.remove("blur");
            mascote.classList.add("no-blur"); //nova alteração
            mascoteMessage.classList.add("no-blur"); //nova alteração
            indice++;
            element.onclick = nextElement; // Avança para o próximo elemento ao clicar //nova alteração
        } else {
            document.getElementById("mascote").style.display = "none";
            document.getElementById("mascote-message").style.display = "none";
            document.querySelectorAll("body > *").forEach(el => el.classList.remove("blur"));
        }
    }

    nextElement();
}

// Função que verifica se o user já escolheu se quer ajuda ou não
window.onload = async function () {
    let wantHelp = await confirmCustom("Queres que te mostre o meu site?", "Sim quero a tua ajuda", "Agora não, obrigado");
    localStorage.setItem("wantHelp", wantHelp);
    if (wantHelp) {
        document.getElementById("mascote").style.display = "block";
        document.getElementById("mascote-message").style.display = "block";
        startHelp();
    }
}