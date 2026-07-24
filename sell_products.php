<link rel="stylesheet" href="/Project_IMS/assests/css/sell_products.css">

<article class="panel" id="sales">
    <div class="panel-head">
         <div>
            <p class="panel-tag">Sale</p>
            <h2>Sell Product</h2>
        </div>
    </div>

                    <form id="saleForm" class="stack-form">
                        <label>
                            Product
                            <select id="saleProduct" required></select>
                        </label>
                        <label>
                            Quantity Sold
                            <input type="number" id="saleQuantity" min="1" step="1" value="1" required>
                        </label>
                        <button type="submit" class="primary-btn">Complete Sale</button>
                    </form>
                </article>