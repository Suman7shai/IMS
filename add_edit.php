
<link rel="stylesheet" href="/Project_IMS/assests/css/add_edit.css">

                <article class="panel" id="manage-product">
                    <div class="panel-head">
                        <div>
                            <p class="panel-tag">Add / Edit</p>
                            <h2>Product Form</h2>
                        </div>
                    </div>

                    <form id="productForm" class="stack-form">
                        <input type="hidden" id="productId">
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
                            <button type="button" class="secondary-btn" id="clearFormBtn">Clear</button>
                        </div>
                    </form>
                </article>