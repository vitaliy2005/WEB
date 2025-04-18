$(document).ready(function() {
    // Динамическая фильтрация через AJAX
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'catalog.php',
            method: 'GET',
            data: $(this).serialize(),
            success: function(response) {
                $('#cars-list').html($(response).find('#cars-list').html());
                // Обновление пагинации
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