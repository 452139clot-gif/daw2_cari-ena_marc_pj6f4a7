// js/app.js
// Se ejecuta cuando el DOM está listo
document.addEventListener('DOMContentLoaded', () => {
  // Referencias a elementos del DOM
  const form = document.getElementById('orderForm');
  const resultBox = document.getElementById('result');


  // 1) Activar/desactivar input de cantidad cuando se marca/desmarca el checkbox
  //    - .enable-check: checkbox que habilita un producto
  //    - .qty: input de cantidad vinculado por data-index
  document.querySelectorAll('.enable-check').forEach(chk => {
    chk.addEventListener('change', (e) => {
      const idx = e.target.dataset.index; // índice del producto
      const qtyInput = document.querySelector(`.qty[data-index="${idx}"]`);
      if (e.target.checked) {
        // Si el checkbox está marcado: habilitamos y ponemos foco para facilitar entrada
        qtyInput.disabled = false;
        qtyInput.focus();
      } else {
        // Si lo desmarcamos: deshabilitamos y reseteamos el valor a 1
        qtyInput.disabled = true;
        qtyInput.value = 1;
      }
    });
  });



  // 2) Manejo del envío del formulario
  form.addEventListener('submit', async (ev) => {
    ev.preventDefault(); // Evita la recarga por defecto del formulario

    // Reset visual del resultBox y mensaje de "procesando"
    resultBox.classList.remove('success', 'error');
    resultBox.style.display = 'none'; // ocultamos momentáneamente
    resultBox.textContent = 'Processing...';


    // 3) Construimos el payload (objeto que enviaremos al servidor)
    const payload = {
      order_code: form.order_code.value.trim(),
      full_name: form.full_name.value.trim(),
      email: form.email.value.trim(),
      address: form.address.value.trim(),
      phone: form.phone.value.trim(),
      items: []
    };


    // 4) Recolectamos info de cada producto presente en el DOM
    document.querySelectorAll('.product').forEach((el) => {
      const nameEl = el.querySelector('.pname');
      const priceEl = el.querySelector('.pprice span[data-price]');
      const checkEl = el.querySelector('.enable-check');
      const qtyEl = el.querySelector('.qty');

      // Comprobamos que los elementos existen antes de leerlos por robustez
      if (!nameEl || !priceEl || !checkEl || !qtyEl) return;

      const name = nameEl.textContent.trim();
      const price = parseFloat(priceEl.dataset.price); // toma precio del atributo data-price
      const enabled = checkEl.checked;
      const qty = parseInt(qtyEl.value, 10) || 0;

      // Solo añadimos si está activado y la cantidad es positiva
      if (enabled && qty > 0) {
        payload.items.push({ name, price, quantity: qty });
      }
    });


    // 5) Validación simple en el cliente
    if (!payload.order_code || !payload.full_name || !payload.email) {
      resultBox.textContent = 'Please fill order code, full name and email.';
      resultBox.classList.add('error');
      resultBox.style.display = 'block';
      return;
    }

    if (payload.items.length === 0) {
      resultBox.textContent = 'Please select at least one product and set its quantity.';
      resultBox.classList.add('error');
      resultBox.style.display = 'block';
      return;
    }


    // 6) Intentamos enviar la petición al servidor con fetch
    try {
      const resp = await fetch('../srv/process_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      // Si el servidor responde con status no OK, leemos el texto y lanzamos error
      if (!resp.ok) {
        const text = await resp.text();
        throw new Error(`Server error: ${resp.status} ${text}`);
      }

      // Interpretamos la respuesta como JSON
      const data = await resp.json();

      
      // 7) Mostramos resultados según lo que devuelva el servidor
      if (data.success) {
        resultBox.innerHTML = `<strong>Order processed successfully.</strong><br>${escapeHtml(data.message)}<br><em>Server returned: ${escapeHtml(data.formatted_total)}</em>`;
        resultBox.classList.add('success');
        resultBox.style.display = 'block';
      } else {
        resultBox.innerHTML = `<strong>Error:</strong> ${escapeHtml(data.message || 'Unknown error')}`;
        resultBox.classList.add('error');
        resultBox.style.display = 'block';
      }
    } catch (err) {
      // En caso de cualquier error (network, parseo, etc.)
      console.error(err);
      resultBox.innerHTML = `Error sending order: ${escapeHtml(err.message)}`;
      resultBox.classList.add('error');
      resultBox.style.display = 'block';
    }
  });
});
