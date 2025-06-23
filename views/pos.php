oducts(data);
            })
            .catch(error => {
                console.error('Error loading products:', error);
                alert('Gagal memuat produk: ' + error.message);
            });
        }

        function displayProducts(products) {
            const grid = document.getElementById('productsGrid');
            
            if (products.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #6c757d;">Tidak ada produk ditemukan</div>';
                return;
            }

            grid.innerHTML = products.map(product => `
                <div class="product-card" onclick="addToCart(${product.id})">
                    <div class="product-image">📦</div>
                    <div class="product-name">${escapeHtml(product.name)}</div>
                    <div class="product-price">Rp ${formatNumber(product.price)}</div>
                    <div class="product-stock">Stok: ${product.stock} ${escapeHtml(product.unit)}</div>
                </div>
            `).join('');
        }

        function addToCart(productId) {
            const product = products.find(p => p.id == productId);
            if (!product || product.stock <= 0) {
                alert('Produk tidak tersedia atau stok habis');
                return;
            }

            const existingItem = cart.find(item => item.id == productId);
            if (existingItem) {
                if (existingItem.quantity < product.stock) {
                    existingItem.quantity++;
                    existingItem.total = existingItem.quantity * existingItem.price;
                } else {
                    alert('Stok tidak mencukupi');
                    return;
                }
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: 1,
                    total: parseFloat(product.price),
                    stock: product.stock
                });
            }

            updateCartDisplay();
        }

        function updateQuantity(productId, change) {
            const item = cart.find(item => item.id == productId);
            if (!item) return;

            const newQuantity = item.quantity + change;
            if (newQuantity <= 0) {
                removeFromCart(productId);
                return;
            }

            if (newQuantity > item.stock) {
                alert('Stok tidak mencukupi');
                return;
            }

            item.quantity = newQuantity;
            item.total = item.quantity * item.price;
            updateCartDisplay();
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.id != productId);
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartSummary = document.getElementById('cartSummary');

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <p>Keranjang masih kosong</p>
                        <small>Pilih produk untuk memulai transaksi</small>
                    </div>
                `;
                cartSummary.style.display = 'none';
                return;
            }

            cartItems.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <div class="item-info">
                        <div class="item-name">${escapeHtml(item.name)}</div>
                        <div class="item-price">Rp ${formatNumber(item.price)} x ${item.quantity}</div>
                    </div>
                    <div class="item-controls">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <input type="number" class="qty-input" value="${item.quantity}" 
                               onchange="setQuantity(${item.id}, this.value)" min="1" max="${item.stock}">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        <span class="remove-btn" onclick="removeFromCart(${item.id})">🗑️</span>
                    </div>
                </div>
            `).join('');

            // Calculate totals
            const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
            const tax = subtotal * 0.1;
            const total = subtotal + tax;

            document.getElementById('subtotal').textContent = 'Rp ' + formatNumber(subtotal);
            document.getElementById('tax').textContent = 'Rp ' + formatNumber(tax);
            document.getElementById('total').textContent = 'Rp ' + formatNumber(total);

            cartSummary.style.display = 'block';
            calculateChange();
        }

        function setQuantity(productId, quantity) {
            const item = cart.find(item => item.id == productId);
            if (!item) return;

            quantity = parseInt(quantity);
            if (quantity <= 0) {
                removeFromCart(productId);
                return;
            }

            if (quantity > item.stock) {
                alert('Stok tidak mencukupi');
                return;
            }

            item.quantity = quantity;
            item.total = item.quantity * item.price;
            updateCartDisplay();
        }

        function calculateChange() {
            const total = cart.reduce((sum, item) => sum + item.total, 0) * 1.1; // Include tax
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const change = paymentAmount - total;

            document.getElementById('changeAmount').value = change >= 0 ? 'Rp ' + formatNumber(change) : 'Rp 0';
        }

        function processTransaction() {
            if (cart.length === 0) {
                alert('Keranjang masih kosong');
                return;
            }

            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
            const tax = subtotal * 0.1;
            const total = subtotal + tax;

            if (paymentAmount < total) {
                alert('Jumlah pembayaran kurang');
                return;
            }

            // Disable button and show loading
            const processBtn = document.getElementById('processBtn');
            processBtn.disabled = true;
            processBtn.textContent = 'Memproses...';
            processBtn.classList.add('loading');

            const transactionData = {
                items: cart.map(item => ({
                    product_id: parseInt(item.id),
                    quantity: parseInt(item.quantity),
                    unit_price: parseFloat(item.price),
                    total_price: parseFloat(item.total)
                })),
                subtotal: parseFloat(subtotal),
                tax_amount: parseFloat(tax),
                total_amount: parseFloat(total),
                payment_method: document.getElementById('paymentMethod').value,
                payment_amount: parseFloat(paymentAmount),
                change_amount: parseFloat(paymentAmount - total)
            };

            console.log('Sending transaction data:', transactionData);

            const formData = new FormData();
            formData.append('action', 'process_transaction');
            formData.append('data', JSON.stringify(transactionData));

            fetch('pos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed response:', data);
                    
                    if (data.success) {
                        alert('Transaksi berhasil! Kode: ' + (data.transaction_code || 'N/A'));
                        // Reset cart
                        cart = [];
                        updateCartDisplay();
                        document.getElementById('paymentAmount').value = '';
                        document.getElementById('changeAmount').value = '';
                        // Reload products to update stock
                        loadProducts('');
                    } else {
                        alert('Error: ' + (data.message || 'Transaksi gagal'));
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response text:', text);
                    alert('Error parsing response: ' + e.message);
                }
            })
            .catch(error => {
                console.error('Error processing transaction:', error);
                alert('Terjadi kesalahan saat memproses transaksi: ' + error.message);
            })
            .finally(() => {
                // Re-enable button
                processBtn.disabled = false;
                processBtn.textContent = 'Proses Transaksi';
                processBtn.classList.remove('loading');
            });
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>