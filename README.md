# MBWAY-Payment-Processor-Plugin-for-Guru-LMS

## Introduction

The MBWAY Payment Processor Plugin integrates MBWAY, a popular payment method in Portugal, into the Guru LMS platform. Once installed, this plugin adds MBWAY as a payment option on the cart page and allows for various configurations from the plugin setting page.

## Features

- Adds MBWAY as a payment option in Guru LMS.
- Configurable plugin settings including dynamic plugin name, MBWAY key, and phishing key.
- Callback URL for processing notifications from Ifthenpay and updating order status.
- Supports multiple languages (English and Portuguese).
- Secure handling of sensitive information such as the phishing key.

## Installation

### 1. Download the Plugin

Obtain the MBWAY payment plugin ZIP file from the (https://github.com/shivamkathyala/MBWAY-Payment-Processor-Plugin-for-Guru-LMS) page.

### 2. Install the Plugin

- Navigate to **Extensions > Manage > Install** in Joomla.
- Upload the plugin file and click on **Upload & Install**.

### 3. Activate the Plugin

- Go to **Extensions > Plugins**.
- Search for **MBWAY Payment Processor**.
- Enable the plugin.

## Configuration

### 1. Access Plugin Settings

- Go to **Components > Guru LMS > Payment Methods**.
- Click on **MBWAY** to open the plugin settings page.

### 2. Set Dynamic Plugin Name

- Enter the desired dynamic name in the **Dynamic Name** field. This name will be displayed on the cart page and as the payment method in the order manager page.

### 3. Enter MBWAY Key

- Locate the **MBWAY Key** field.
- Enter the key provided by Ifthenpay.

### 4. Enter Phishing Key

- Locate the **Phishing Key** field.
- Enter the phishing key provided by Ifthenpay.

### 5. Configure Callback URL

- Copy the **Approved Callback URL** from the plugin settings page.
- Log in to the Ifthenpay back office.
- Navigate to the settings for your MBWAY account and paste the callback URL.
- Add the phishing key in the appropriate field.

### 6. Save Settings

- Click **Save** or **Save & Close** to apply the changes.

## Usage

### 1. Customer Checkout

- When a customer proceeds to checkout on Guru LMS, MBWAY will be listed as a payment option.
- The dynamic name set in the plugin settings will be displayed as the payment method name.

### 2. Order Management

- Orders placed using MBWAY will reflect the dynamic name set in the plugin on the order manager page.
- The order status will be updated based on the callback received from Ifthenpay.

## Technical Details

### 1. Plugin Files

- **Main plugin file**: `mbway.php`
- **Additional files**: Logo image and an MP3 file for notifications.

### 2. Callback Handling

- The plugin includes a callback URL that processes notifications from Ifthenpay and updates the order status in Guru LMS accordingly.

### 3. Language Support

- The plugin supports multiple languages (English and Portuguese).
- Language constants are defined in language folders (e.g., `en-GB`, `pt-PT`).

### 4. Security

- Ensure that the phishing key is kept confidential and only shared with Ifthenpay as required.
- The callback URL should be secured with HTTPS to protect against tampering.

## Troubleshooting

### 1. Payment Not Showing on Cart Page

- Verify that the plugin is enabled and configured correctly.
- Check that the dynamic plugin name is set and saved properly.

### 2. Order Status Not Updating

- Ensure that the callback URL is correctly set in the Ifthenpay back office.
- Confirm that the phishing key matches the one configured in the plugin settings.

### 3. Language Issues

- Ensure that language files are correctly placed in the respective folders.
- Verify that the constants are correctly defined and translated.

## Contact

For any questions or issues, please contact on email: shivamkathyala@gmail.com

## Acknowledgements

- Thanks to [Ifthenpay](https://www.ifthenpay.com) for providing the MBWAY API.
- Thanks to the [Guru LMS](https://guru.ijoomla.com) team for the great learning management system.