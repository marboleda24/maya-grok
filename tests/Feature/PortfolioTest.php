test('portfolio page loads', function () {
    $response = get('/portfolios');
    $response->assertStatus(200); // Ajusta según tu ruta
});