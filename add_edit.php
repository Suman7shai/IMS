<?php
$editId = isset($_GET['edit']) ? trim($_GET['edit']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add / Edit Product</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/add_edit.css">
</head>
<body>
    <main class="page-shell">
        <article class="panel" id="manage-product">
            <div class="panel-head">
                <div>
                    <p class="panel-tag">Add / Edit</p>
                    <h2>Product Form</h2>
                </div>
                <a class="secondary-btn" href="/Project_IMS/dashboard.php">Back to Dashboard</a>
            </div>

            <form id="productForm" class="stack-form" autocomplete="off">
                <input type="hidden" id="productId" value="">
                <label>
                    Product Name
                    <input type="text" id="productName" required>
                </label>
                <label>
                    Category
                    <input type="text" id="productCategory" required>
                </label>
                <div class="form-row">
                    <label>
                        Price
                        <input type="number" id="productPrice" min="0" step="0.01" required>
                    </label>
                    <label>
                        Stock
                        <input type="number" id="productStock" min="0" step="1" required>
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="primary-btn" id="saveProductBtn">Save Product</button>
                    <button type="button" class="secondary1-btn" id="clearFormBtn">Clear</button>
                </div>
            </form>
        </article>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const storageKey = 'ims-products';
            const editId = <?= json_encode($editId); ?>;

            const elements = {
                productForm: document.getElementById('productForm'),
                productId: document.getElementById('productId'),
                productName: document.getElementById('productName'),
                productCategory: document.getElementById('productCategory'),
                productPrice: document.getElementById('productPrice'),
                productStock: document.getElementById('productStock'),
                saveProductBtn: document.getElementById('saveProductBtn'),
                clearFormBtn: document.getElementById('clearFormBtn')
            };

            function loadProducts() {
                try {
                    const saved = localStorage.getItem(storageKey);
                    return saved ? JSON.parse(saved) : [];
                } catch {
                    return [];
                }
            }

            function saveProducts(products) {
                localStorage.setItem(storageKey, JSON.stringify(products));
            }

            function fillForm(product) {
                elements.productId.value = product.id;
                elements.productName.value = product.name || '';
                elements.productCategory.value = product.category || '';
                elements.productPrice.value = product.price ?? '';
                elements.productStock.value = product.stock ?? '';
                elements.saveProductBtn.textContent = 'Update Product';
            }

            function clearForm() {
                elements.productId.value = '';
                elements.productName.value = '';
                elements.productCategory.value = '';
                elements.productPrice.value = '';
                elements.productStock.value = '';
                elements.saveProductBtn.textContent = 'Save Product';
            }

            const products = loadProducts();
            if (editId) {
                const product = products.find((item) => item.id === editId);
                if (product) {
                    fillForm(product);
                }
            }

            elements.productForm.addEventListener('submit', (event) => {
                event.preventDefault();

                const name = elements.productName.value.trim();
                const category = elements.productCategory.value.trim();
                const price = Number(elements.productPrice.value);
                const stock = Number(elements.productStock.value);
                const currentId = elements.productId.value || editId;

                if (!name || !category || Number.isNaN(price) || Number.isNaN(stock)) {
                    alert('Please complete all product fields.');
                    return;
                }

                const currentProducts = loadProducts();
                if (currentId) {
                    const updatedProducts = currentProducts.map((product) => {
                        if (product.id !== currentId) {
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

                    saveProducts(updatedProducts);
                } else {
                    currentProducts.unshift({
                        id: crypto.randomUUID(),
                        name,
                        category,
                        price,
                        stock
                    });

                    saveProducts(currentProducts);
                }

                window.location.href = '/Project_IMS/dashboard.php';
            });

            elements.clearFormBtn.addEventListener('click', clearForm);
        });
    </script>
</body>
</html>
