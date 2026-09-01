document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'mobile-sidebar-toggle';
        toggle.setAttribute('aria-label', 'Toggle menu');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '<span></span><span></span><span></span>';

        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';

        document.body.insertBefore(toggle, document.body.firstChild);
        document.body.appendChild(overlay);

        const closeSidebar = () => {
            document.body.classList.remove('sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        const openSidebar = () => {
            document.body.classList.add('sidebar-open');
            toggle.setAttribute('aria-expanded', 'true');
        };

        toggle.addEventListener('click', () => {
            const isOpen = document.body.classList.contains('sidebar-open');
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        document.querySelectorAll('.sidebar-nav a, .nav-parent').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 980) {
                    closeSidebar();
                }
            });
        });
    }

    const storageKeys = {
        products: 'ims-products',
        sales: 'ims-sales',
        updated: 'ims-last-updated'
    };

    const demoProducts = [
        { id: crypto.randomUUID(), name: 'Wireless Mouse', category: 'Accessories', price: 18.99, stock: 42 },
        { id: crypto.randomUUID(), name: 'Office Keyboard', category: 'Accessories', price: 29.5, stock: 18 },
        { id: crypto.randomUUID(), name: 'LED Monitor', category: 'Display', price: 159.99, stock: 9 },
        { id: crypto.randomUUID(), name: 'USB Cable', category: 'Cables', price: 4.99, stock: 120 }
    ];

    const els = {
        currentDate: document.getElementById('currentDate'),
        lastUpdated: document.getElementById('lastUpdated'),
        totalProducts: document.getElementById('totalProducts'),
        totalUnits: document.getElementById('totalUnits'),
        lowStock: document.getElementById('lowStock'),
        unitsSold: document.getElementById('unitsSold'),
        searchInput: document.getElementById('searchInput'),
        resetDemo: document.getElementById('resetDemo'),
        productTableBody: document.getElementById('productTableBody'),
        productForm: document.getElementById('productForm'),
        productId: document.getElementById('productId'),
        productName: document.getElementById('productName'),
        productCategory: document.getElementById('productCategory'),
        productPrice: document.getElementById('productPrice'),
        productStock: document.getElementById('productStock'),
        saveProductBtn: document.getElementById('saveProductBtn'),
        clearFormBtn: document.getElementById('clearFormBtn'),
        saleForm: document.getElementById('saleForm'),
        saleProduct: document.getElementById('saleProduct'),
        saleQuantity: document.getElementById('saleQuantity'),
        activityList: document.getElementById('activityList')
    };

    const currencyFormatter = new Intl.NumberFormat('en-NP', {
        style: 'currency',
        currency: 'NPR',
    });

    let products = loadProducts();
    let sales = loadSales();
    

    function loadProducts() {
        const saved = localStorage.getItem(storageKeys.products);
        if (!saved) {
            localStorage.setItem(storageKeys.products, JSON.stringify(demoProducts));
            return [...demoProducts];
        }

        try {
            const parsed = JSON.parse(saved);
            return Array.isArray(parsed) && parsed.length ? parsed : [...demoProducts];
        } catch {
            return [...demoProducts];
        }
    }

    function loadSales() {
        const saved = localStorage.getItem(storageKeys.sales);
        if (!saved) {
            localStorage.setItem(storageKeys.sales, JSON.stringify([]));
            return [];
        }

        try {
            const parsed = JSON.parse(saved);
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    function saveState() {
        localStorage.setItem(storageKeys.products, JSON.stringify(products));
        localStorage.setItem(storageKeys.sales, JSON.stringify(sales));
        localStorage.setItem(storageKeys.updated, new Date().toISOString());
    }

    function formatDateTime(value) {
        return new Intl.DateTimeFormat('en-US', {
            dateStyle: 'medium',
            timeStyle: 'short'
        }).format(new Date(value));
    }

    function updateHeaderTime() {
        const now = new Date();

        els.currentDate.textContent = new Intl.DateTimeFormat('en-NP', {
            // weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            // second: '2-digit'
        }).format(now);

        const updated = localStorage.getItem(storageKeys.updated);
        els.lastUpdated.textContent = updated ? formatDateTime(updated) : formatDateTime(now);
    }

    function getFilteredProducts() {
        const query = els.searchInput.value.trim().toLowerCase();
        if (!query) {
            return products;
        }

        return products.filter((product) => {
            return [product.name, product.category]
                .join(' ')
                .toLowerCase()
                .includes(query);
        });
    }

    function getStatus(stock) {
        if (stock <= 0) {
            return { label: 'Out of stock', className: 'out' };
        }

        if (stock <= 10) {
            return { label: 'Low stock', className: 'low' };
        }

        return { label: 'In stock', className: 'ok' };
    }

    function renderStats() {
        const totalProducts = products.length;
        const totalUnits = products.reduce((sum, product) => sum + Number(product.stock), 0);
        const lowStock = products.filter((product) => Number(product.stock) > 0 && Number(product.stock) <= 10).length;
        const unitsSold = sales.reduce((sum, sale) => sum + Number(sale.quantity), 0);

        els.totalProducts.textContent = totalProducts;
        els.totalUnits.textContent = totalUnits;
        els.lowStock.textContent = lowStock;
        els.unitsSold.textContent = unitsSold;
    }

    function renderTable() {
        const visibleProducts = getFilteredProducts();

        if (!visibleProducts.length) {
            els.productTableBody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="empty-state">No products found. Try a different search or add a new product.</div>
                    </td>
                </tr>
            `;
            return;
        }

        els.productTableBody.innerHTML = visibleProducts.map((product) => {
            const status = getStatus(product.stock);
            return `
                <tr>
                    <td>
                        <strong>${escapeHtml(product.name)}</strong><br>
                        <span style="color: var(--muted); font-size: 0.88rem;">ID: ${product.id.slice(0, 8)}</span>
                    </td>
                    <td>${escapeHtml(product.category)}</td>
                    <td>${currencyFormatter.format(product.price)}</td>
                    <td>${product.stock}</td>
                    <td><span class="badge ${status.className}">${status.label}</span></td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="action-btn edit" data-action="edit" data-id="${product.id}">Edit</button>
                            <button type="button" class="action-btn sell" data-action="sell" data-id="${product.id}">Sell</button>
                            <button type="button" class="action-btn delete" data-action="delete" data-id="${product.id}">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderSaleOptions() {
        if (!els.saleProduct || !els.saleQuantity) {
            return;
        }

        const options = products
            .map((product) => `<option value="${product.id}">${escapeHtml(product.name)} (${product.stock})</option>`)
            .join('');

        els.saleProduct.innerHTML = options || '<option value="">No products available</option>';
        els.saleProduct.disabled = products.length === 0;
        els.saleQuantity.disabled = products.length === 0;
    }

    function renderActivity() {
        if (!sales.length) {
            els.activityList.innerHTML = '<div class="empty-state">No sales recorded yet. Complete a sale to see activity here.</div>';
            return;
        }

        els.activityList.innerHTML = sales.slice(0, 8).map((sale) => `
            <div class="activity-item">
                <div>
                    <strong>${escapeHtml(sale.productName)}</strong>
                    <span>${sale.quantity} unit(s) sold • ${formatDateTime(sale.timestamp)}</span>
                </div>
                <span class="badge ${sale.stockAfter <= 10 ? 'low' : 'ok'}">Stock left: ${sale.stockAfter}</span>
            </div>
        `).join('');
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function clearForm() {
        els.productId.value = '';
        els.productName.value = '';
        els.productCategory.value = '';
        els.productPrice.value = '';
        els.productStock.value = '';
        els.saveProductBtn.textContent = 'Save Product';
    }

    function fillForm(product) {
        els.productId.value = product.id;
        els.productName.value = product.name;
        els.productCategory.value = product.category;
        els.productPrice.value = product.price;
        els.productStock.value = product.stock;
        els.saveProductBtn.textContent = 'Update Product';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showMessage(message) {
        alert(message);
    }

    function refreshUI() {
        saveState();
        renderStats();
        renderTable();
        renderSaleOptions();
        renderActivity();
        updateHeaderTime();
    }

    if (els.productForm) {
        els.productForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const name = els.productName.value.trim();
            const category = els.productCategory.value.trim();
            const price = Number(els.productPrice.value);
            const stock = Number(els.productStock.value);
            const existingId = els.productId.value;

            if (!name || !category || Number.isNaN(price) || Number.isNaN(stock)) {
                showMessage('Please complete all product fields.');
                return;
            }

            if (existingId) {
                products = products.map((product) => {
                    if (product.id !== existingId) {
                        return product;
                    }

                    return {
                        ...product,
                        name,
                        category,
                        price,
                        stock
                    };
                });
            } else {
                products.unshift({
                    id: crypto.randomUUID(),
                    name,
                    category,
                    price,
                    stock
                });
            }

            clearForm();
            refreshUI();
        });
    }

    if (els.clearFormBtn) {
        els.clearFormBtn.addEventListener('click', clearForm);
    }

    if (els.saleForm) {
        els.saleForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const productId = els.saleProduct.value;
            const quantity = Number(els.saleQuantity.value);
            const product = products.find((item) => item.id === productId);

            if (!product) {
                showMessage('Select a valid product.');
                return;
            }

            if (!quantity || quantity < 1) {
                showMessage('Enter a valid quantity.');
                return;
            }

            if (product.stock < quantity) {
                showMessage('Not enough stock for this sale.');
                return;
            }

            product.stock -= quantity;

            sales.unshift({
                id: crypto.randomUUID(),
                productId: product.id,
                productName: product.name,
                quantity,
                stockAfter: product.stock,
                timestamp: new Date().toISOString()
            });

            els.saleQuantity.value = 1;
            refreshUI();
            showMessage(`Sale completed. ${quantity} unit(s) removed from stock.`);
        });
    }

    if (els.productTableBody) {
        els.productTableBody.addEventListener('click', (event) => {
            const button = event.target.closest('[data-action]');
            if (!button) {
                return;
            }

        const { action, id } = button.dataset;
        const product = products.find((item) => item.id === id);
        if (!product) {
            return;
        }

            if (action === 'edit') {
                localStorage.setItem('ims-edit-product', JSON.stringify(product));
                window.location.href = `/Project_IMS/add_edit.php?edit=${encodeURIComponent(id)}`;
                return;
            }

            if (action === 'sell' && els.saleForm && els.saleProduct && els.saleQuantity) {
                els.saleProduct.value = id;
                els.saleQuantity.value = 1;
                els.saleForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (action === 'delete') {
                const confirmed = confirm(`Delete ${product.name}? This cannot be undone.`);
                if (!confirmed) {
                    return;
                }

                products = products.filter((item) => item.id !== id);
                if (els.productId && els.productId.value === id) {
                    clearForm();
                }
                refreshUI();
            }
        });
    }

    if (els.searchInput) {
        els.searchInput.addEventListener('input', renderTable);
    }

    if (els.resetDemo) {
        els.resetDemo.addEventListener('click', () => {
            const confirmed = confirm('Reset dashboard to demo products?');
            if (!confirmed) {
                return;
            }

            products = [...demoProducts];
            sales = [];
            clearForm();
            refreshUI();
        });
    }

    refreshUI();

    setInterval(updateHeaderTime, 1000);


    const ensureLogoutButton = () => {
        let logoutBtn = document.getElementById('logoutBtn');

        if (!logoutBtn) {
            logoutBtn = document.createElement('button');
            logoutBtn.type = 'button';
            logoutBtn.id = 'logoutBtn';
            logoutBtn.className = 'logout-btn';
            logoutBtn.textContent = 'Logout';

            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.appendChild(logoutBtn);
            } else {
                logoutBtn.style.position = 'fixed';
                logoutBtn.style.right = '20px';
                logoutBtn.style.bottom = '20px';
                document.body.appendChild(logoutBtn);
            }
        }

        return logoutBtn;
    };

    const logoutBtn = ensureLogoutButton();
    const modalOverlay = document.getElementById('logoutModalOverlay') || (() => {
        const overlay = document.createElement('div');
        overlay.id = 'logoutModalOverlay';
        overlay.className = 'logout-modal-overlay';
        overlay.innerHTML = `
            <div class="logout-modal" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
                <div class="logout-modal-icon">⎋</div>
                <h3 id="logoutModalTitle">Log out?</h3>
                <p>Are you sure you want to sign out of your dashboard?</p>
                <div class="logout-modal-actions">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="button" class="confirm-btn">Logout</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    })();

    const hideLogoutModal = () => modalOverlay.classList.remove('show');

    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modalOverlay.classList.add('show');
    });

    modalOverlay.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            hideLogoutModal();
        }
    });

    const cancelBtn = modalOverlay.querySelector('.cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', hideLogoutModal);
    }

    const confirmBtn = modalOverlay.querySelector('.confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            window.location.href = '/Project_IMS/auth/logout.php';
        });
    }
});

document.querySelectorAll('.nav-item.has-submenu').forEach((item) => {
    const parentBtn = item.querySelector('.nav-parent');

    parentBtn.addEventListener('click', () => {
        const isOpen = item.classList.toggle('open');
        parentBtn.setAttribute('aria-expanded', isOpen);
    });
});