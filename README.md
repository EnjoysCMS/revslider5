# composer.json
```json
...,
"extra": {
// сюда вставляются перечисленные ниже параметры
}
```

```json
//Отображается в админке
"module-name": "Название модуля"
```

```json
//Использование миграций, если екгу должная быть директория ./migrations
"use-migrations": true // or false
```

```json
//Подменю в админке
"admin-links": {
  "register/route": "Описание ссылки",
  ...,
  ...,
}
```

# blocks.yml

Если в модуле будут блоки, нужно использовать структуру описанную ниже.

```yaml
App\Module\MyModule\Blocks\ClassBlock: #Класс блока полностью
  name: Название блока
  options: #Параметры <array>
    option_name: #Уникальный ключ параметра
      value: Значение параметра
      name: Название параметра
      description: Описание параметра
...
```