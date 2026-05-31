<?php

// Генерация хеша для пароля администратора
echo password_hash('admin123', PASSWORD_DEFAULT);

?>