# CardGate official Magento2 payments module #

The official CardGate module that offers a direct connection with the CardGate RESTful payment service.

## Preparation ##

The usage of this module requires that you have obtained CardGate RESTful API credentials.
Please visit https://my.cardgate.com/ and retrieve your RESTful API username and password or contact your accountmanager.

## Installation ##

- Extract the package into /app/ so the path /app/code/Cardgate/Payment/ exists after extraction.
- Make sure the Magento user has read privileges in /app/code/Cardgate/
- Run /bin/magento setup:upgrade
- Optional: Run /bin/magento setup:di:compile

## Configuration

- Open the Magento Admin panel and view the CardGate plugin-configuration:
  - Stores > Configuration
  - Sales > CardGate
- Fill in for "CardGate configuration" section
- Save Config
- To refesh the active paymentmethods, click: 
  - "CardGate information" section
  - Paymentmethods
  - Refresh active paymentmethods button 
  - Check the output
- After the paymentmethods are refreshed successfully, all active Paymentmethod-sections can be edited.