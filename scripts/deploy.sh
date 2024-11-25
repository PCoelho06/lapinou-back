#!/bin/bash

curl -X POST "https://webhooks.hostinger.com/deploy/b5e3bd8244942df0fe0b3f28a1eca93f" \
     -H "Content-Type: application/json" \
     -d "{\"branch\": \"$1\", \"action\": \"deploy\"}"

POST_DEPLOYMENT_SCRIPT="/home/u309746653/domains/lapinou.tech/public_html/api/scripts/post-deployment.sh"

if [ -f "$POST_DEPLOYMENT_SCRIPT" ]; then
  echo "Running post-deployment script..."
  bash "$POST_DEPLOYMENT_SCRIPT" || { echo "Post-deployment script failed. Aborting."; exit 1; }
else
  echo "Error: Post-deployment script not found at $POST_DEPLOYMENT_SCRIPT. Aborting."
  exit 1
fi

echo "Deployment script completed!"
