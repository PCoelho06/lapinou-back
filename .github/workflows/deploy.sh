#!/bin/bash
curl -X POST "https://webhooks.hostinger.com/deploy/b5e3bd8244942df0fe0b3f28a1eca93f" \
     -H "Content-Type: application/json" \
     -d "{\"branch\": \"$1\", \"action\": \"deploy\"}"