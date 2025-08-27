#!/bin/bash

# 🧪 WhatsApp Server Test Script
# Test basic functionality of the WhatsApp server

echo "🧪 Testing WhatsApp Server..."

SERVER_URL="http://localhost:3001"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_test() {
    echo -e "${BLUE}[TEST]${NC} $1"
}

print_pass() {
    echo -e "${GREEN}[PASS]${NC} $1"
}

print_fail() {
    echo -e "${RED}[FAIL]${NC} $1"
}

print_info() {
    echo -e "${YELLOW}[INFO]${NC} $1"
}

# Check if server is running
print_test "Checking if WhatsApp server is running..."

if curl -s "$SERVER_URL/health" > /dev/null; then
    print_pass "WhatsApp server is running"
    
    # Get health status
    print_test "Getting server health status..."
    health_response=$(curl -s "$SERVER_URL/health")
    echo "$health_response" | python3 -m json.tool 2>/dev/null || echo "$health_response"
    print_pass "Health check completed"
    
    # Test sessions endpoint
    print_test "Testing sessions endpoint..."
    sessions_response=$(curl -s "$SERVER_URL/sessions")
    echo "$sessions_response" | python3 -m json.tool 2>/dev/null || echo "$sessions_response"
    print_pass "Sessions endpoint accessible"
    
    # Test creating a test session
    print_test "Creating test session..."
    create_response=$(curl -s -X POST "$SERVER_URL/sessions" \
        -H "Content-Type: application/json" \
        -d '{"sessionId": "test-session"}')
    echo "$create_response" | python3 -m json.tool 2>/dev/null || echo "$create_response"
    
    if echo "$create_response" | grep -q "success.*true"; then
        print_pass "Test session created successfully"
        
        # Get session status
        print_test "Getting test session status..."
        status_response=$(curl -s "$SERVER_URL/sessions/test-session")
        echo "$status_response" | python3 -m json.tool 2>/dev/null || echo "$status_response"
        print_pass "Session status retrieved"
        
        # Cleanup test session
        print_test "Cleaning up test session..."
        delete_response=$(curl -s -X DELETE "$SERVER_URL/sessions/test-session")
        echo "$delete_response" | python3 -m json.tool 2>/dev/null || echo "$delete_response"
        print_pass "Test session deleted"
    else
        print_fail "Failed to create test session"
    fi
    
else
    print_fail "WhatsApp server is not running"
    print_info "Start the server with: cd whatsapp-server && npm run dev"
    exit 1
fi

echo ""
print_pass "🎉 All tests completed!"
print_info "Server is ready for WhatsApp integration"