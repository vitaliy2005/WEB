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