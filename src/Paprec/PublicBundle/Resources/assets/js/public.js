$(function () {

    $('.addProductToQuoteButton').on('click', function () {

        $('.addProductToQuoteButton').prop("disabled", true); // On désactive tous les boutons
        const productId = $(this).data('product');
        const url = $(this).data('url');
        const qtty = $('#quantityProductSelect_' + productId).val();

        $.ajax({
            url: url,
            type: "POST",
            data: {
                "productId": productId,
                "quantity": qtty
            },
            success: function (response) {
                // On récupère l'HTML du du produit ajouté et on l'insère dans le récap du devis (=panier)
                var htmlToDisplay = response.trim();
                $("#devis-recap-item-" + productId).remove();
                $("#devis-recap").append(htmlToDisplay);
            },
            complete: function () {
                $('.addProductToQuoteButton').prop("disabled", false); // Element(s) are now enabled.
            }
        });
    });

});





