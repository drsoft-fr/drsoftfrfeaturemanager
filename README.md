![drSoft.fr](logo.png)

# drSoft.fr Feature manager

## Table of contents

- [Presentation](#Presentation)
- [Requirements](#Requirements)
- [Install](#Install)
- [Links](#Links)
- [Authors](#Authors)
- [Licenses](#Licenses)

## Presentation

PrestaShop module for managing product characteristics.

Software developed by drSoft.fr.

## Requirements

This module requires PrestaShop 8.1.0 to work correctly.

This library also requires :

for production :

- [composer](https://getcomposer.org/)

for development :

- [composer](https://getcomposer.org/)
- [eslint](https://eslint.org/)
- [prettier](https://prettier.io/)
- [npm](https://www.npmjs.com/)
- [Vue.js](https://vuejs.org/)
- [tailwindcss](https://tailwindcss.com/)
- [daisyUI](https://daisyui.com/)
- [vite](https://vitejs.dev/)
- [TypeScript](https://www.typescriptlang.org/index.html)

## Install

```bash
$ cd {PRESTASHOP_FOLDER}/modules
$ git clone git@github.com:drsoft-fr/prestashop-module-drsoftfrfeaturemanager.git
$ mv prestashop-module-drsoftfrfeaturemanager drsoftfrfeaturemanager
$ cd drsoftfrfeaturemanager
$ composer install -o --no-dev
$ cd {PRESTASHOP_FOLDER}
$ php ./bin/console prestashop:module install drsoftfrfeaturemanager
```

### Build assets

Build assets for production

```bash
$ npm install
$ npm run build
```

Build assets for development

```bash
$ npm install
$ npm run watch
```

## Links

- [drSoft.fr on GitHub](https://github.com/drsoft-fr)
- [GitHub](https://github.com/drsoft-fr/prestashop-module-drsoftfrfeaturemanager)
- [Issues](https://github.com/drsoft-fr/prestashop-module-drsoftfrfeaturemanager/issues)

## Authors

**Dylan Ramos** - [on GitHub](https://github.com/dylan-ramos)

## Licenses

see LICENSE file
