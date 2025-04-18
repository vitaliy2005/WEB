#!/bin/bash

# Создаем директорию проекта
mkdir -p car_market

# Переходим в директорию проекта
cd car_market || exit

# Создаем основные директории
mkdir -p css js/images/uploads includes

# Создаем основные файлы
touch index.php catalog.php car.php add_car.php login.php register.php profile.php logout.php

# Создаем файлы в поддиректориях
touch css/styles.js js/scripts.js includes/db.php includes/header.php includes/footer.php

# Выводим сообщение об успешном создании
echo "Структура проекта car_market успешно создана!"

# Выводим дерево директорий для проверки
tree
