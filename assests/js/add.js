
document.addEventListener('DOMContentLoaded', () => {
const storageKey = 'ims-products';
const editId =`<?= json_encode($editId); ?>`;

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