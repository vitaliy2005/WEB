$(document).ready(function() {
    // Динамическая загрузка моделей
    $('#brand_id').change(function() {
        const brand_id = $(this).val();
        $.ajax({
            url: 'get_models.php',
            method: 'GET',
            data: { brand_id: brand_id },
            success: function(data) {
                $('#model_id').html(data);
            }
        });
    });

    // Динамическая фильтрация через AJAX
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'catalog.php',
            method: 'GET',
            data: $(this).serialize(),
            success: function(response) {
                $('#cars-list').html($(response).find('#cars-list').html());
                $('nav').html($(response).find('nav').html());
            }
        });
    });

    // Динамический поиск с задержкой
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            $('#filter-form').submit();
        }, 500);
    });
});

// Показ номера телефона
function showPhone(carId) {
    const phone = document.getElementById('phone-' + carId);
    phone.style.display = 'block';
    fetch('get_phone.php?id=' + carId)
        .then(response => response.text())
        .then(data => phone.innerText = data);
}

function isAdmin($user_id) {
    if (!is_numeric($user_id) || $user_id <= 0) {
        return false;
    }

    global $conn;
    
    // Проверяем соединение с БД
    if (!$conn || $conn->connect_error) {
        error_log("Database connection error in isAdmin() function");
        return false;
    }

    try {
        $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }

        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }

        $result = $stmt->get_result();
        if (!$result) {
            error_log("Get result failed: " . $stmt->error);
            return false;
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        // Проверяем, что запись найдена и is_admin = 1
        return !empty($user) && isset($user['is_admin']) && $user['is_admin'] == 1;
        
    } catch (Exception $e) {
        error_log("Error in isAdmin function: " . $e->getMessage());
        return false;
    }
}