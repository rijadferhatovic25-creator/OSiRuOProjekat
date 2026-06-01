#!/bin/bash

URL="http://localhost:8080"
LOG_FILE="health.log"

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$URL")

if [ "$HTTP_CODE" -eq 200 ]; then
    echo "$TIMESTAMP - HTTP $HTTP_CODE - OK" >> "$LOG_FILE"
    exit 0
elif [ "$HTTP_CODE" -ge 100 ] 2>/dev/null; then
    echo "$TIMESTAMP - HTTP $HTTP_CODE - ERROR" >> "$LOG_FILE"
    exit 1
else
    echo "$TIMESTAMP - CONNECTION FAILED" >> "$LOG_FILE"
    exit 2
fi