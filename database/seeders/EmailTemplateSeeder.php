    private function getMonthlyReportTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 30px; background-color: #34495e; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 26px; margin: 0; text-align: center;">📊 Monthly Report</h1>
                            <p style="color: #bdc3c7; font-size: 16px; margin: 10px 0 0 0; text-align: center;">{{current_date}}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 22px; margin: 0 0 20px 0;">Hello {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Here's your monthly performance summary with key metrics and insights.
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td width="45%" style="padding: 15px; background-color: #ecf0f1; border-radius: 5px;">
                                        <h4 style="color: #2c3e50; font-size: 14px; margin: 0 0 5px 0; text-transform: uppercase;">Total Contacts</h4>
                                        <p style="color: #27ae60; font-size: 24px; font-weight: bold; margin: 0;">{{total_contacts}}</p>
                                    </td>
                                    <td width="10%"></td>
                                    <td width="45%" style="padding: 15px; background-color: #ecf0f1; border-radius: 5px;">
                                        <h4 style="color: #2c3e50; font-size: 14px; margin: 0 0 5px 0; text-transform: uppercase;">Campaigns Sent</h4>
                                        <p style="color: #3498db; font-size: 24px; font-weight: bold; margin: 0;">{{campaigns_sent}}</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin: 30px 0;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 15px 0;">Key Highlights</h3>
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Email open rate increased by 15%</li>
                                    <li>Added 25 new qualified leads</li>
                                    <li>Completed 3 successful campaigns</li>
                                    <li>Customer engagement up 23%</li>
                                </ul>
                            </div>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #34495e; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: 500; display: inline-block;">View Full Report</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getReEngagementTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Miss You</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #2c3e50; font-size: 32px; margin: 0;">We Miss You! 💙</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 24px; margin: 0 0 20px 0;">Hi {{first_name}},</h2>
                            <p style="color: #666666; font-size: 18px; line-height: 1.6; margin: 0 0 25px 0;">
                                It's been a while since we last heard from you, and we wanted to reach out to see how you're doing.
                            </p>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                We've been busy improving our platform with exciting new features that we think you'll love:
                            </p>
                            <div style="background-color: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0;">
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>🚀 Enhanced dashboard with real-time analytics</li>
                                    <li>📱 Mobile app for on-the-go management</li>
                                    <li>🤖 AI-powered insights and recommendations</li>
                                    <li>🔗 New integrations with popular tools</li>
                                </ul>
                            </div>
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="#" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 30px; font-weight: bold; font-size: 16px; display: inline-block;">Welcome Back - 30% Off</a>
                            </div>
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 0; text-align: center;">
                                Use code COMEBACK30 - valid for the next 7 days
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
