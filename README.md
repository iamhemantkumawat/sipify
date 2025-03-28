# Setting up Poof.io

1. Create a Poof.io Account:
• Go to Poof.io and sign up for an account if you haven't already.

2. Enable Cryptocurrency Payments:
• Navigate to "Payments" and "Link" in your Poof.io dashboard.
• Enable BTC, ETH, and LTC as payment options.

3. Generate API Key and Shared Secret:
• Create an API key in Poof.io and save it in your database.
• Generate a "Shared Secret" in Poof.io and store it in your database.

4. Set Up Webhook for Auto Payments:
• In Poof.io, add a webhook URL for auto payments.
• The URL should be http://yourserverip/payments/webhook.php. Replace yourserverip with your web host's IP or domain.



# Configuring Database

1. Add Magnus IP to Settings Table:
• In the "settings" table of your database, add your Magnus IP.

2. Define MagnusBilling Default Plan ID:
• Define your MagnusBilling default plan ID in the database.

3. Save MagnusBilling API Credentials:
• Generate an API key and API secret in MagnusBilling for your root user.
• Store these credentials in the database.



# Final Steps

1. Update Database Configuration:
• Modify the database configuration in database/config.php to match your database credentials.

2. Upload Files to Server:
• Upload all necessary files to your cPanel or VPS, ensuring that they are in the correct directories.


