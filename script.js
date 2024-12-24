// Example using the fetch API to make a request to your signup endpoint
fetch('/api/V1/auth/signup', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        username: 'newuser',
        password: 'password123'
    })
})
.then(response => response.json())
.then(data => {
    // Handle the response from the API
})
.catch(error => {
    // Handle errors
});