<?php
/**
 * Punto de Venta (POS) - Proyecto Bee
 * Interfaz de venta rapida con busqueda de productos y clientes
 */

$pageTitle = 'Punto de Venta';
$currentModule = 'sales';
$currentPage = 'pos';

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/sales.functions.php';
require_once ROOT_PATH . '/includes/functions/clients.functions.php';

$session = Session::getInstance();
$user = $session->getUser();

// Procesar venta (AJAX) — antes de emitir HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    switch ($_POST['action']) {
        case 'search_products':
            $query = $_POST['query'] ?? '';
            $products = salesSearchProducts($query);
            echo json_encode(['success' => true, 'products' => $products]);
            exit;

        case 'search_clients':
            $query = $_POST['query'] ?? '';
            $clientsList = clientsSearch($query);
            echo json_encode(['success' => true, 'clients' => $clientsList]);
            exit;

        case 'complete_sale':
            $items = json_decode($_POST['items'] ?? '[]', true);
            $clientId = $_POST['client_id'] ?? null;
            $total = floatval($_POST['total'] ?? 0);

            if (empty($items)) {
                echo json_encode(['success' => false, 'message' => 'No hay productos en la venta.']);
                exit;
            }

            $saleId = salesCreate([
                'client_id' => $clientId,
                'user_id'   => $user['id'],
                'total'     => $total,
                'items'     => $items
            ]);

            if ($saleId) {
                echo json_encode(['success' => true, 'sale_id' => $saleId, 'message' => 'Venta #' . $saleId . ' completada.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al procesar la venta.']);
            }
            exit;
    }
}

$pageTitle = 'Punto de Venta';
$currentModule = 'sales';
$currentPage = 'pos';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

// Obtener productos iniciales para mostrar
$initialProducts = salesSearchProducts('');
?>

<!-- Estilos especificos POS inline -->
<style>
.pos-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: var(--spacing-lg);
    height: calc(100vh - var(--header-height) - 6rem);
    min-height: 500px;
}
@media (max-width: 900px) {
    .pos-layout { grid-template-columns: 1fr; height: auto; }
}
.pos-products { display: flex; flex-direction: column; }
.pos-cart { display: flex; flex-direction: column; }
.pos-search { margin-bottom: var(--spacing-md); }
.pos-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: var(--spacing-sm);
    overflow-y: auto;
    flex: 1;
    padding-right: var(--spacing-xs);
}
.pos-product-card {
    background: var(--color-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    padding: var(--spacing-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}
.pos-product-card:hover {
    border-color: var(--color-secondary);
    box-shadow: var(--shadow-sm);
    transform: translateY(-1px);
}
.pos-product-card .prod-name {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-semibold);
    margin-bottom: 2px;
    line-height: 1.3;
}
.pos-product-card .prod-price {
    font-size: var(--font-size-base);
    font-weight: var(--font-weight-bold);
    color: #2a8a20;
}
.pos-product-card .prod-stock {
    font-size: var(--font-size-xs);
    color: var(--color-muted);
}
.pos-cart-items {
    flex: 1;
    overflow-y: auto;
    margin-bottom: var(--spacing-sm);
}
.pos-cart-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm);
    border-bottom: 1px solid var(--color-border);
    font-size: var(--font-size-sm);
    gap: var(--spacing-sm);
}
.pos-cart-item .item-info { flex: 1; min-width: 0; }
.pos-cart-item .item-name { font-weight: var(--font-weight-medium); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pos-cart-item .item-detail { font-size: var(--font-size-xs); color: var(--color-muted); }
.pos-cart-item .item-qty { display: flex; align-items: center; gap: 4px; }
.pos-cart-item .item-qty button {
    width: 24px; height: 24px; border: 1px solid var(--color-border); border-radius: 4px;
    background: var(--color-primary); cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: bold; color: var(--color-text);
}
.pos-cart-item .item-qty span { width: 28px; text-align: center; font-weight: var(--font-weight-semibold); }
.pos-cart-item .item-subtotal { font-weight: var(--font-weight-semibold); min-width: 70px; text-align: right; }
.pos-cart-item .item-remove {
    background: none; border: none; cursor: pointer; color: var(--color-muted);
    padding: 2px; transition: color var(--transition-fast);
}
.pos-cart-item .item-remove:hover { color: var(--color-error); }
.pos-cart-totals {
    border-top: 2px solid var(--color-border);
    padding-top: var(--spacing-sm);
}
.pos-cart-total-row {
    display: flex; justify-content: space-between;
    padding: var(--spacing-xs) 0;
    font-size: var(--font-size-sm);
}
.pos-cart-total-row.final {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    border-top: 1px dashed var(--color-border);
    padding-top: var(--spacing-sm);
    margin-top: var(--spacing-xs);
}
.client-search-result {
    background: var(--color-primary);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    max-height: 200px;
    overflow-y: auto;
    display: none;
    position: absolute;
    z-index: 10;
    width: 100%;
    box-shadow: var(--shadow-md);
}
.client-search-result .client-item {
    padding: var(--spacing-sm);
    cursor: pointer;
    border-bottom: 1px solid var(--color-border);
    font-size: var(--font-size-sm);
}
.client-search-result .client-item:hover { background: rgba(255,244,117,0.15); }
.pos-client-area { position: relative; margin-bottom: var(--spacing-sm); }
.selected-client {
    display: flex; align-items: center; justify-content: space-between;
    padding: var(--spacing-xs) var(--spacing-sm); background: rgba(111,255,92,0.1);
    border-radius: var(--border-radius); font-size: var(--font-size-sm); margin-bottom: var(--spacing-sm);
}
.selected-client button {
    background: none; border: none; cursor: pointer; color: var(--color-muted);
    padding: 2px; font-size: 16px;
}
</style>

<div class="toolbar">
    <div class="toolbar-left">
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold)">Punto de Venta</h1>
    </div>
    <div class="toolbar-right">
        <a href="history.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Historial
        </a>
    </div>
</div>

<div class="pos-layout">
    <!-- Panel Productos -->
    <div class="card pos-products">
        <div class="card-header">
            <h2 class="card-title">Productos</h2>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;flex:1;overflow:hidden">
            <div class="pos-search search-input" style="width:100%">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="productSearch" placeholder="Buscar producto por nombre o codigo..." autocomplete="off">
            </div>
            <div class="pos-product-grid" id="productGrid">
                <?php foreach ($initialProducts as $prod): ?>
                <div class="pos-product-card" onclick="addToCart(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['name'], ENT_QUOTES); ?>', <?php echo $prod['price']; ?>, <?php echo $prod['stock']; ?>)">
                    <div class="prod-name"><?php echo htmlspecialchars($prod['name']); ?></div>
                    <div class="prod-price">S/ <?php echo number_format($prod['price'], 2); ?></div>
                    <div class="prod-stock">Stock: <?php echo $prod['stock']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Panel Carrito -->
    <div class="card pos-cart">
        <div class="card-header">
            <h2 class="card-title">Venta Actual</h2>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;flex:1;overflow:hidden">
            <!-- Cliente -->
            <div class="pos-client-area">
                <div class="search-input" style="width:100%">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <input type="text" id="clientSearch" placeholder="Buscar cliente (opcional)..." autocomplete="off">
                </div>
                <div class="client-search-result" id="clientResults"></div>
            </div>
            <div class="selected-client" id="selectedClient" style="display:none">
                <span id="selectedClientName"></span>
                <button onclick="removeClient()" title="Quitar cliente">&times;</button>
            </div>
            <input type="hidden" id="clientId" value="">

            <!-- Items -->
            <div class="pos-cart-items" id="cartItems">
                <div style="text-align:center;padding:var(--spacing-xl);color:var(--color-muted)">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.3;margin-bottom:8px">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <p>Seleccione productos</p>
                </div>
            </div>

            <!-- Totales -->
            <div class="pos-cart-totals">
                <div class="pos-cart-total-row">
                    <span>Subtotal</span>
                    <span id="subtotal">S/ 0.00</span>
                </div>
                <div class="pos-cart-total-row final">
                    <span>TOTAL</span>
                    <span id="totalDisplay">S/ 0.00</span>
                </div>
            </div>

            <button class="btn btn-primary btn-lg btn-block" onclick="completeSale()" style="margin-top:var(--spacing-md)" id="btnComplete" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>
                Completar Venta
            </button>
        </div>
    </div>
</div>

<script>
// ─── Carrito POS ─────────────────────────────
let cart = [];
let selectedClient = { id: null, name: '' };

function updateCartDisplay() {
    const container = document.getElementById('cartItems');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('totalDisplay');
    const btnComplete = document.getElementById('btnComplete');

    if (cart.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:var(--spacing-xl);color:var(--color-muted)"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.3;margin-bottom:8px"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><p>Seleccione productos</p></div>';
        btnComplete.disabled = true;
        subtotalEl.textContent = 'S/ 0.00';
        totalEl.textContent = 'S/ 0.00';
        return;
    }

    const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
    let html = '';

    cart.forEach((item, index) => {
        html += `
            <div class="pos-cart-item">
                <div class="item-info">
                    <div class="item-name">${escapeHtml(item.name)}</div>
                    <div class="item-detail">S/ ${item.price.toFixed(2)} c/u</div>
                </div>
                <div class="item-qty">
                    <button onclick="updateQty(${index}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQty(${index}, 1)">+</button>
                </div>
                <div class="item-subtotal">S/ ${item.subtotal.toFixed(2)}</div>
                <button class="item-remove" onclick="removeItem(${index})" title="Eliminar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>`;
    });

    container.innerHTML = html;
    subtotalEl.textContent = 'S/ ' + total.toFixed(2);
    totalEl.textContent = 'S/ ' + total.toFixed(2);
    btnComplete.disabled = false;
}

function addToCart(id, name, price, stock) {
    const existing = cart.find(item => item.product_id === id);
    if (existing) {
        if (existing.quantity >= stock) {
            alert('Stock insuficiente (disponible: ' + stock + ')');
            return;
        }
        existing.quantity++;
        existing.subtotal = existing.quantity * existing.price;
    } else {
        cart.push({ product_id: id, name, price, quantity: 1, subtotal: price });
    }
    updateCartDisplay();
}

function updateQty(index, delta) {
    const item = cart[index];
    const newQty = item.quantity + delta;
    if (newQty <= 0) {
        cart.splice(index, 1);
    } else {
        item.quantity = newQty;
        item.subtotal = newQty * item.price;
    }
    updateCartDisplay();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

// ─── Busqueda Productos ─────────────────────
let searchTimeout;
document.getElementById('productSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length === 0) {
        searchTimeout = setTimeout(() => searchProducts(''), 100);
        return;
    }

    searchTimeout = setTimeout(() => searchProducts(query), 300);
});

function searchProducts(query) {
    fetch('pos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=search_products&query=' + encodeURIComponent(query)
    })
    .then(res => res.json())
    .then(data => {
        const grid = document.getElementById('productGrid');
        if (!data.products || data.products.length === 0) {
            grid.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--color-muted);grid-column:1/-1">No se encontraron productos</div>';
            return;
        }
        grid.innerHTML = data.products.map(p => `
            <div class="pos-product-card" onclick="addToCart(${p.id}, '${escapeHtml(p.name)}', ${p.price}, ${p.stock})">
                <div class="prod-name">${escapeHtml(p.name)}</div>
                <div class="prod-price">S/ ${parseFloat(p.price).toFixed(2)}</div>
                <div class="prod-stock">Stock: ${p.stock}</div>
            </div>`).join('');
    });
}

// ─── Busqueda Clientes ──────────────────────
let clientSearchTimeout;
document.getElementById('clientSearch').addEventListener('input', function() {
    clearTimeout(clientSearchTimeout);
    const query = this.value.trim();
    if (query.length < 2) {
        document.getElementById('clientResults').style.display = 'none';
        return;
    }
    clientSearchTimeout = setTimeout(() => searchClients(query), 300);
});

function searchClients(query) {
    fetch('pos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=search_clients&query=' + encodeURIComponent(query)
    })
    .then(res => res.json())
    .then(data => {
        const results = document.getElementById('clientResults');
        if (!data.clients || data.clients.length === 0) {
            results.innerHTML = '<div class="client-item" style="color:var(--color-muted)">No se encontraron clientes</div>';
        } else {
            results.innerHTML = data.clients.map(c =>
                `<div class="client-item" onclick="selectClient(${c.id}, '${escapeHtml(c.name)}', '${escapeHtml(c.document_type || '')}', '${escapeHtml(c.document_number || '')}')">
                    <strong>${escapeHtml(c.name)}</strong>
                    <span style="color:var(--color-muted);font-size:var(--font-size-xs)">${escapeHtml(c.document_type || '')}: ${escapeHtml(c.document_number || '')}</span>
                </div>`).join('');
        }
        results.style.display = 'block';
    });
}

function selectClient(id, name) {
    selectedClient = { id, name };
    document.getElementById('clientId').value = id;
    document.getElementById('selectedClientName').textContent = 'Cliente: ' + name;
    document.getElementById('selectedClient').style.display = 'flex';
    document.getElementById('clientResults').style.display = 'none';
    document.getElementById('clientSearch').value = '';
}

function removeClient() {
    selectedClient = { id: null, name: '' };
    document.getElementById('clientId').value = '';
    document.getElementById('selectedClient').style.display = 'none';
}

// ─── Completar Venta ────────────────────────
function completeSale() {
    if (cart.length === 0) {
        alert('Agregue productos al carrito.');
        return;
    }

    if (!confirm('¿Confirmar la venta por S/ ' + cart.reduce((s, i) => s + i.subtotal, 0).toFixed(2) + '?')) return;

    const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const items = cart.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
        price: item.price,
        subtotal: item.subtotal
    }));

    const btn = document.getElementById('btnComplete');
    btn.disabled = true;
    btn.textContent = 'Procesando...';

    fetch('pos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=complete_sale&items=' + encodeURIComponent(JSON.stringify(items)) +
              '&client_id=' + (selectedClient.id || '') +
              '&total=' + total
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Redirigir al comprobante
            window.location.href = 'receipt.php?id=' + data.sale_id;
        } else {
            alert(data.message || 'Error al procesar la venta.');
            btn.disabled = false;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg> Completar Venta';
        }
    })
    .catch(err => {
        alert('Error de conexion.');
        btn.disabled = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg> Completar Venta';
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cerrar dropdown de clientes al hacer click afuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.pos-client-area')) {
        document.getElementById('clientResults').style.display = 'none';
    }
});
</script>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
