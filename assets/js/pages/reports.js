$(document).ready(function () {
 loadProducts();
});

function loadProducts() {
    $.ajax({
        url: './?action=getallproducts',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            console.log('Products loaded successfully:', data);
            var products = data;
            var productSelect = $('#product_id');
            productSelect.empty();
            productSelect.append('<option value="0">TODOS</option>');
            products.forEach(function (product) {
                productSelect.append($('<option>', {
                    value: product.id,
                    text: product.name
                }));
            });
        },
        error: function (xhr, status, error) {
            console.error('Error loading products:', error);
        }
    });

    // fetch(`./?action=getallproducts`)
    //     .then(response => response.json())
    //     .then(productData => {            
    //         var productSelect = $('#product_id');
    //         productSelect.empty();
    //         productSelect.append('<option value="0">TODOS</option>');
    //         productData.forEach(function (product) {
    //             productSelect.append($('<option>', {
    //                 value: product.id,
    //                 text: product.name
    //             }));
    //         });
    //     })
    //     .catch(error => {
    //         console.error('Error al obtener los datos del producto:', error);
    //     });
}