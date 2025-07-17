<!-- Chat Launcher Button -->
<button id="chat-launcher"
        class="position-fixed bottom-0 end-0 m-3 shadow"
        style="width: 56px; height: 56px; border-radius: 50%; background-color: #FF3333; border: none; z-index: 1050; display: flex; align-items: center; justify-content: center;">
  <i class="bi bi-chat-dots-fill" style="font-size: 1.3rem; color: white;"></i>
</button>

<!-- Chatbot Window -->
<div id="chatbot-window"
     class="position-fixed bottom-0 end-0 m-3 border-0 rounded-3 shadow bg-white d-none"
     style="width: 300px; height: 450px; z-index: 1051; display: flex; flex-direction: column;">

  <!-- Fejléc (fixen marad) -->
  <div class="chatbot-header p-3 d-flex justify-content-between align-items-center"
       style="background-color: #ff3333; color: white; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;">
    <strong>Kérdezz tőlünk</strong>
    <button type="button" class="btn-close btn-close-white btn-sm" id="chatbot-close" aria-label="Bezárás"></button>
  </div>

  <!-- Görgethető tartalom -->
  <div class="chatbot-body p-3" style="overflow-y: auto; flex-grow: 1;">
    <div id="chatbot-questions" class="d-grid gap-2 mb-4">
      <button class="btn btn-outline-secondary btn-sm chat-question" data-question="1">Mennyi idő a szállítás?</button>
      <button class="btn btn-outline-secondary btn-sm chat-question" data-question="2">Hogyan tudok regisztrálni vagy kapcsolatba lépni?</button>
      <button class="btn btn-outline-secondary btn-sm chat-question" data-question="3">Milyen fizetési módok érhetők el?</button>
      <button class="btn btn-outline-secondary btn-sm chat-question" data-question="4">Hol tudok szállítási címet megadni?</button>
    </div>

    <div id="chatbot-answer-area" class="pt-2"></div>
  </div>
</div>

<script>
  const answers = {
    1: [
      "Általában a szállítási idő 1–3 munkanap, amennyiben a termék raktáron van.",
      "Érdeklődjön munkatársainknál a megadott elérhetőségeken."
    ],
    2: [
      "Keress minket a megadott elérhetőségeken, vagy vedd fel velünk a kapcsolatot a kapcsolati űrlapon.",
      "Nagyon gyors kiszolgálás és versenyképes árak, folyamatosan bővülő termékpaletta vár!"
    ],
    3: [
      "Utalással vagy készpénzzel átvételkor tudsz fizetni.",
      "Jelenleg bankkártyás fizetés nem elérhető az oldalon."
    ],
    4: [
      "A felhasználói fiók ‘Adatok szerkesztése’ menüpontjában adhatod meg vagy módosíthatod a címeidet.",
      "A rendelés véglegesítésekor kiválaszthatod, melyik címre kéred a csomagot."
    ]
  };

  const followupPrompts = {
    1: "Honnan tudhatom, hogy raktáron van-e a termék?",
    2: "Miért érdemes regisztrálni?",
    3: "Bankkártyával tudok fizetni az oldalon?",
    4: "A rendelésnél tudok választani közülük?"
  };

  const avatar = "https://cdn-icons-png.flaticon.com/512/747/747545.png";

  let followupIndex = {};

  document.querySelectorAll('.chat-question').forEach(button => {
    button.addEventListener('click', () => {
      const key = button.dataset.question;
      const target = document.getElementById('chatbot-answer-area');
      document.getElementById('chatbot-questions').classList.add('d-none');

      if (!followupIndex[key]) {
        followupIndex[key] = 0;

        const userMsg = `
          <div class="d-flex flex-row justify-content-end mb-3">
            <div class="p-3 me-2 border bg-body-tertiary text-dark" style="border-radius: 15px;">
              <p class="small mb-0">${button.innerText}</p>
            </div>
            <img src="${avatar}" alt="user avatar" style="width: 45px; height: 45px; border-radius: 50%;">
          </div>`;
        target.insertAdjacentHTML('beforeend', userMsg);
      }

      if (followupIndex[key] < answers[key].length) {
        const botMsg = `
          <div class="d-flex flex-row justify-content-start mb-3">
            <img src="${avatar}" alt="bot avatar" style="width: 45px; height: 45px; border-radius: 50%;">
            <div class="p-3 ms-2" style="border-radius: 15px; background-color: rgba(255, 51, 51, 0.1);">
              <p class="small mb-0 text-dark">${answers[key][followupIndex[key]]}</p>
            </div>
          </div>`;
        target.insertAdjacentHTML('beforeend', botMsg);
        followupIndex[key]++;

        // Második kérdés megjelenítése (ha még nem volt és van még válasz)
        if (followupIndex[key] === 1 && followupPrompts[key]) {
          const followupBtn = document.createElement('button');
          followupBtn.className = 'btn btn-outline-danger btn-sm mt-2';
          followupBtn.textContent = followupPrompts[key];
          followupBtn.onclick = () => {
  // 1. Távolítsuk el az előző „Vissza a kérdésekhez” gombot
  const allBackBtns = target.querySelectorAll('.back-btn');
  allBackBtns.forEach(btn => btn.remove());

  // 2. Felhasználói utókérdés beszúrása
  const userFollowup = `
    <div class="d-flex flex-row justify-content-end mb-3">
      <div class="p-3 me-2 border bg-light text-dark" style="border-radius: 15px;">
        <p class="small mb-0">${followupPrompts[key]}</p>
      </div>
      <img src="${avatar}" alt="user avatar" style="width: 32px; height: 32px; border-radius: 50%;">
    </div>`;
  target.insertAdjacentHTML('beforeend', userFollowup);

  // 3. További kérdés gomb eltüntetése
  followupBtn.remove();

  // 4. Bot válasz indítása
  button.click();
};


          target.appendChild(followupBtn);
        }

        // Vissza gomb minden kör után
        const backBtn = document.createElement('button');
        backBtn.className = 'btn btn-link btn-sm text-decoration-none text-muted mt-3 back-btn';

        backBtn.innerHTML = '&larr; Vissza a kérdésekhez';
        backBtn.onclick = () => {
          followupIndex = {};
          target.innerHTML = '';
          document.getElementById('chatbot-questions').classList.remove('d-none');
        };
        target.appendChild(backBtn);
      }
    });
  });

  // Chat megnyitás
  document.getElementById('chat-launcher').addEventListener('click', () => {
    document.getElementById('chatbot-window').classList.remove('d-none');
    document.getElementById('chat-launcher').classList.add('d-none');
  });

  // Chat bezárás
  document.getElementById('chatbot-close').addEventListener('click', () => {
    document.getElementById('chatbot-window').classList.add('d-none');
    document.getElementById('chat-launcher').classList.remove('d-none');
  });
</script>
