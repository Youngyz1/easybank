import os
import json
import urllib3

http = urllib3.PoolManager()
slack_url = os.environ['SLACK_WEBHOOK_URL']

def lambda_handler(event, context):
    for record in event['Records']:
        sns_message = record['Sns']['Message']
        alarm_name = record['Sns']['Subject'] if 'Subject' in record['Sns'] else "Alarm"
        payload = {
            "text": f":warning: *{alarm_name}*\n```{sns_message}```"
        }
        resp = http.request(
            "POST",
            slack_url,
            body=json.dumps(payload),
            headers={'Content-Type': 'application/json'}
        )
        print(f"Sent to Slack, status: {resp.status}")
    return {"status": "ok"}