<?php
/**
 * Formulario de Cliente - Proyecto Bee
 * Crear y editar clientes
 */

require_once '../../includes/config/constants.php';
require_once ROOT_PATH . '/includes/classes/Session.php';
require_once ROOT_PATH . '/includes/functions/auth.functions.php';
require_once ROOT_PATH . '/includes/functions/clients.functions.php';

$session = Session::getInstance();
$client = null;
$isEdit = false;

if (!empty($_GET['id'])) {
    $client = clientsGetById($_GET['id']);
    if ($client) {
        $isEdit = true;
    }
}

// Procesar formulario (antes de emitir HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $session->setFlash('error', 'Token de seguridad invalido.');
    } else {
        // Combinar prefijo + numero de telefono
        $phonePrefix = sanitizeInput($_POST['phone_prefix'] ?? '');
        $phoneNumber = preg_replace('/[^0-9]/', '', $_POST['phone_number'] ?? '');
        $phone = '';
        if (!empty($phonePrefix) && !empty($phoneNumber)) {
            $phone = $phonePrefix . $phoneNumber;
        }

        $data = [
            'id'              => $_POST['id'] ?? null,
            'document_type'   => sanitizeInput($_POST['document_type'] ?? 'V'),
            'document_number' => sanitizeInput($_POST['document_number'] ?? ''),
            'name'            => sanitizeInput($_POST['name'] ?? ''),
            'email'           => sanitizeInput($_POST['email'] ?? ''),
            'phone'           => $phone,
            'address'         => sanitizeInput($_POST['address'] ?? ''),
            'status'          => sanitizeInput($_POST['status'] ?? 'activo'),
            'notes'           => sanitizeInput($_POST['notes'] ?? '')
        ];

        if (empty($data['name'])) {
            $session->setFlash('error', 'El nombre del cliente es obligatorio.');
        } else {
            clientsSave($data);
            $session->setFlash('success', 'Cliente guardado correctamente.');
            header('Location: list.php');
            exit;
        }
    }
}

$pageTitle = $isEdit ? 'Editar Cliente' : 'Formulario Cliente';
$currentModule = 'clients';
$currentPage = 'list';
require_once TEMPLATES_PATH . '/partials/layout-start.php';

if (!$client) {
    $client = [
        'id' => '', 'document_type' => 'V', 'document_number' => '',
        'name' => '', 'email' => '', 'phone' => '', 'address' => '', 'status' => 'activo', 'notes' => ''
    ];
}

// Separar telefono en prefijo y numero para el form
$phonePrefix = '';
$phoneNumber = '';
$phoneDisplay = '';
if (!empty($client['phone'])) {
    $phone = $client['phone'];
    $prefixes = ['0412', '0422', '0414', '0424', '0416', '0426'];
    foreach ($prefixes as $p) {
        if (strpos($phone, $p) === 0) {
            $phonePrefix = $p;
            $phoneNumber = substr($phone, strlen($p));
            break;
        }
    }
    if (empty($phonePrefix) && strlen($phone) >= 4) {
        $phonePrefix = substr($phone, 0, 4);
        $phoneNumber = substr($phone, 4);
    }
    $phoneDisplay = formatPhoneDisplay($phoneNumber);
}

function formatPhoneDisplay($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (strlen($number) >= 5) {
        return substr($number, 0, 3) . ' ' . substr($number, 3, 2) . ' ' . substr($number, 5);
    } elseif (strlen($number) >= 3) {
        return substr($number, 0, 3) . ' ' . substr($number, 3);
    }
    return $number;
}
?>

<div class="toolbar">
    <div class="toolbar-left">
        <a href="list.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <h1 style="font-size:var(--font-size-xl);font-weight:var(--font-weight-bold);margin-left:var(--spacing-sm)">
            <?php echo $isEdit ? 'Editar Cliente' : 'Nuevo Cliente'; ?>
        </h1>
    </div>
</div>

<div class="card">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfToken(); ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
        <?php endif; ?>

        <div class="card-body">
            <div class="form-group">
                <label class="form-label-required">Tipo y Numero Documento</label>
                <div style="display:flex">
                    <select name="document_type" class="form-select" style="width:90px;flex-shrink:0;border-radius:var(--border-radius) 0 0 var(--border-radius);border-right:none">
                        <option value="V" <?php echo $client['document_type'] === 'V' ? 'selected' : ''; ?>>V</option>
                        <option value="G" <?php echo $client['document_type'] === 'G' ? 'selected' : ''; ?>>G</option>
                        <option value="E" <?php echo $client['document_type'] === 'E' ? 'selected' : ''; ?>>E</option>
                    </select>
                    <input type="text" name="document_number" class="form-input" required
                           style="flex:1;border-radius:0 var(--border-radius) var(--border-radius) 0"
                           value="<?php echo htmlspecialchars($client['document_number']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-required">Nombre / Razon Social</label>
                    <input type="text" name="name" class="form-input" required
                           value="<?php echo htmlspecialchars($client['name']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label-required">Estado</label>
                    <select name="status" class="form-select" style="width:100%">
                        <option value="activo" <?php echo $client['status'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $client['status'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-input"
                           value="<?php echo htmlspecialchars($client['email']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label-required">Telefono</label>
                    <div style="display:flex;gap:var(--spacing-xs)">
                        <select name="phone_prefix" class="form-select" style="width:110px;flex-shrink:0">
                            <option value="">--</option>
                            <?php
                            $prefixes = ['0412', '0422', '0414', '0424', '0416', '0426'];
                            foreach ($prefixes as $p):
                                $sel = ($phonePrefix === $p) ? 'selected' : '';
                                echo "<option value=\"$p\" $sel>$p</option>";
                            endforeach;
                            ?>
                        </select>
                    <input type="text" id="phoneNumber" name="phone_number" class="form-input" required
                           style="flex:1" maxlength="9"
                               value="<?php echo htmlspecialchars($phoneDisplay); ?>"
                               placeholder="741 36 75">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Direccion</label>
                <input type="text" name="address" class="form-input"
                       value="<?php echo htmlspecialchars($client['address']); ?>"
                       placeholder="Direccion del cliente">
            </div>

            <div class="form-group">
                <label>Notas</label>
                <textarea name="notes" class="form-textarea" rows="2"><?php echo htmlspecialchars($client['notes']); ?></textarea>
            </div>
        </div>

        <div class="card-footer">
            <div class="form-actions" style="border:none;margin:0;padding:0">
                <a href="list.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $isEdit ? 'Actualizar' : 'Crear'; ?> Cliente
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var phoneInput = document.getElementById('phoneNumber');
    if (!phoneInput) return;

    phoneInput.addEventListener('input', function() {
        var raw = this.value.replace(/[^0-9]/g, '');
        if (raw.length > 7) raw = raw.slice(0, 7);

        var formatted = '';
        if (raw.length >= 5) {
            formatted = raw.slice(0, 3) + ' ' + raw.slice(3, 5) + ' ' + raw.slice(5);
        } else if (raw.length >= 3) {
            formatted = raw.slice(0, 3) + ' ' + raw.slice(3);
        } else {
            formatted = raw;
        }

        this.value = formatted;
    });

    phoneInput.addEventListener('keydown', function(e) {
        // Permitir: numeros, backspace, delete, flechas, tab
        if (
            (e.key >= '0' && e.key <= '9') ||
            e.key === 'Backspace' || e.key === 'Delete' ||
            e.key === 'ArrowLeft' || e.key === 'ArrowRight' ||
            e.key === 'Tab' || e.key === 'Home' || e.key === 'End'
        ) {
            return;
        }
        e.preventDefault();
    });
});
</script>

<?php require_once TEMPLATES_PATH . '/partials/footer.php'; ?>
