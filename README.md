![drSoft.fr](logo.png)

# drSoft.fr Feature Manager Module

## Overview

The **drSoft.fr Feature Manager** is a comprehensive solution for advanced management of product features in PrestaShop.
It extends PrestaShop's native functionality with powerful tools to create, modify, delete, and reorganize features and
their values, as well as manage their associations with products.

## Key Features

### 1. Feature Management

- **Feature Creation** - Simplified interface for adding new features
- **Feature Deletion** - Secure deletion with dependency management
- **Orphaned Features Management** - Identification and cleanup of features without associations

### 2. Feature Values Management

- **Feature Value Creation** - Add new values to existing features
- **Feature Value Deletion** - Secure deletion with impact verification
- **Value Reassignment** - Move a feature value from one feature to another
- **Orphaned Values Management** - Identification and cleanup of values without associations

### 3. Product Association Management

- **Detachment** - Remove association between a product and a feature value
- **Copy** - Add an existing product association to another feature value
- **Move** - Reassign a product from one feature value to another

### 4. Optimized User Interface

- Intuitive tab-organized interface for easy navigation
- Advanced filters and search to quickly locate items to manage
- Consolidated information view for better decision-making

### 5. Database Optimization

- Data cleaning tools to improve performance
- Reduction of database overhead by eliminating unused data

## Installation

### Option 1: Via GitHub

1. Download the latest version of the module from our GitHub
   repository: [https://github.com/drsoft-fr/drsoftfrfeaturemanager](https://github.com/drsoft-fr/drsoftfrfeaturemanager)
2. Unzip the archive and rename the folder to `drsoftfrfeaturemanager`
3. Copy this folder to the `/modules` directory of your PrestaShop installation
4. Go to your store's back office, section "Modules > Module Manager"
5. Search for "drSoft.fr Hook Manager" and click "Install"

### Option 2: Via Terminal (Git)

If you have access to your server's terminal and Git is installed:

```bash
$ cd {PRESTASHOP_FOLDER}/modules
$ git clone git@github.com:drsoft-fr/drsoftfrfeaturemanager.git
$ cd {PRESTASHOP_FOLDER}
$ php ./bin/console prestashop:module install drsoftfrfeaturemanager
```

## Usage

### Module Access

The module is accessible from the Catalog > Feature Manager menu

### Feature Management

- Use the interface to create or delete features
- View all existing features and their associated values

### Feature Values Management

- Add, modify, or delete feature values
- Move values from one feature to another to reorganize your catalog

### Product Association Management

- Detach products from certain feature values
- Copy associations to other feature values
- Move products between different feature values

### Orphaned Elements Management

- Identify and clean up orphaned features and values
- Maintain a clean database to optimize performance

## Technical Architecture

The module is built on PrestaShop's modern architecture, using:

- Services and repositories for data manipulation
- Commands and handlers for complex operations
- A user interface built with Vue.js

## Technical Requirements

- PrestaShop 8.1 or higher
- PHP 8.1 or higher
- Administrative rights on your PrestaShop store

## Support

If you encounter problems or have questions:

1. Check the Issues section on
   GitHub: [https://github.com/drsoft-fr/drsoftfrfeaturemanager/issues](https://github.com/drsoft-fr/drsoftfrfeaturemanager/issues)
2. Create a new Issue if your problem is not already referenced

## License

This module is distributed under the MIT open source license. This means you are free to use, modify, and redistribute
it according to the terms of this license.

For more information, please see the LICENSE file included with this module or
visit [https://opensource.org/licenses/MIT](https://opensource.org/licenses/MIT).

## Links

- [drSoft.fr on GitHub](https://github.com/drsoft-fr)
- [GitHub](https://github.com/drsoft-fr/drsoftfrfeaturemanager)
- [Issues](https://github.com/drsoft-fr/drsoftfrfeaturemanager/issues)
- [www.drsoft.fr](https://www.drsoft.fr)

## Author

**Dylan Ramos** - [on GitHub](https://github.com/dylan-ramos)