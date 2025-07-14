document.addEventListener("DOMContentLoaded", () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function showSpinner() {
        document.getElementById('ajax-spinner').style.display = 'block';
        document.getElementById('cart-body').style.opacity = '0.2';
    }

    function hideSpinner() {
        document.getElementById('ajax-spinner').style.display = 'none';
        document.getElementById('cart-body').style.opacity = '1';
    }

    function updateRowSubtotal(row, unitPrice, quantity) {
        const subtotalCell = row.querySelector('.cart-subtotal');
        const subtotal = unitPrice * quantity;
        subtotalCell.textContent = `${subtotal.toLocaleString()} Ft`;
    }

    function updateRowQuantity(row, quantity) {
        row.querySelector('.cart-quantity').textContent = quantity;
    }

    function recalculateTotal() {
        let total = 0;
        document.querySelectorAll("tr.cart-row").forEach(row => {
            const subtotalText = row.querySelector(".cart-subtotal").textContent;
            const amount = parseInt(subtotalText.replace(/\D/g, "")); // csak számok
            total += amount || 0;
        });

        const totalCell = document.querySelector(".cart-total td:nth-child(2)");
        if (totalCell) {
            totalCell.textContent = `${total.toLocaleString()} Ft +Áfa`;
        }
    }

    // mennyiség módosítás
    document.querySelectorAll(".quantity-increase, .quantity-decrease").forEach(button => {
        button.addEventListener("click", event => {
            event.preventDefault();

            const key = button.dataset.key;
            const change = parseInt(button.dataset.change);
            const row = button.closest("tr.cart-row");
            const quantityElement = row.querySelector(".cart-quantity");
            const currentQuantity = parseInt(quantityElement.textContent);

            if (change === -1 && currentQuantity <= 1) return; // ne menjen 1 alá

            showSpinner();

            fetch("/cart/update", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({ key, change })
            })
            .then(response => {
                if (!response.ok) throw new Error("Nem sikerült frissíteni a mennyiséget.");
                // új mennyiség
                const newQuantity = currentQuantity + change;
                updateRowQuantity(row, newQuantity);

                // egységár kiszámítása (meglévő subtotal / jelenlegi mennyiség)
                const subtotalText = row.querySelector('.cart-subtotal').textContent;
                const currentSubtotal = parseInt(subtotalText.replace(/\D/g, ""));
                const unitPrice = Math.round(currentSubtotal / currentQuantity);
                updateRowSubtotal(row, unitPrice, newQuantity);

                recalculateTotal();
            })
            .catch(error => {
                console.error("AJAX hiba:", error);
                alert("Hiba történt a mennyiség frissítésekor.");
            })
            .finally(hideSpinner);
        });
    });

    //  termék törlés
    document.querySelectorAll(".cart-remove").forEach(button => {
        button.addEventListener("click", event => {
            event.preventDefault();

            const key = button.dataset.key;
            const row = button.closest("tr.cart-row");

            showSpinner();

            fetch("/cart/remove", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({ key })
            })
            .then(response => {
                if (!response.ok) throw new Error("Nem sikerült törölni az elemet.");

                row.remove(); // DOM-ból is eltávolítjuk

                // Ha kiürült a kosár
                if (document.querySelectorAll("tr.cart-row").length === 0) {
                    document.getElementById("cart-body").innerHTML = `<tr><td colspan="6" class="empty-cart">A kosarad üres</td></tr>`;
                } else {
                    recalculateTotal();
                }
            })
            .catch(error => {
                console.error("AJAX hiba:", error);
                alert("Hiba történt a törlés során.");
            })
            .finally(hideSpinner);
        });
    });
});