$(function () {
    'use strict';

    /*******************************************************************************************************************
     * COMMON
     */


    // A la selection d'une option dans le select whoare, on navigue
    $('#whoare-select').on('change', function () {
        var element = $(this).find('option:selected');
        var url = element.data('url');
        $(location).attr('href', url);
    });

    // Si il y a la div step__agency alors on charge son contenu
    if ($('.step__agency').is('div')) {
        reloadNearbyAgencies()
    }

    // Si l'input "locationSelect" existe, alors on lui initialise un autocomplete Google et on force l'input de nombre
    if ($('#locationSelect').is('input')) {
        $('#locationSelect').keypress(function (key) {
            if (key.charCode < 48 || key.charCode > 57) {
                return false;
            }
        });

        if ($('.nonCorporate-form').is('div')) {
            initializeAutocomplete('locationSelect', true);
        } else {
            initializeAutocomplete('locationSelect');
        }
    }

    // Si l'input "divisionSelect" existe, alors on intercepte les modifications de division
    // Quand on sélectionne une division dans le select, on recharge en envoyant la location et la division
    if ($('#divisionSelect').is('select')) {
        var divisionValue = $('#divisionSelect').val();
        $('#divisionSelect').on('change', function () {
            var navigate = true;
            var url = $('#divisionSelect').data('url').replace('divisionTmp', $('#divisionSelect').val());
            // Si on est dans need, alors on affiche une confirm dialog avertissant que le panier sera perdu
            if ($('.need-form').is('div')) {
                Swal({
                    title: "<div class=\"test\">Voulez-vous continuer ?</div>",
                    html: "Cette action va entraîner la <span>perte de votre sélection</span><br>Nous vous conseillons de <span>valider votre besoin</span><br> avant de changer de typologie de déchets.",
                    showCancelButton: true,
                    showCloseButton: true,
                    customClass: 'trash-change-popup',
                    buttons: true,
                    confirmButtonText: "Changer de filière déchet",
                    cancelButtonText: "Annuler",
                    width: '630px',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $(location).attr('href', url);
                    } else {
                        navigate = false;
                        $('#divisionSelect').val(divisionValue);
                    }
                });
            } else {
                $(location).attr('href', url);
            }
        });
    }

    // Si l'input "frequencyRadioBox" existe, alors on intercepte les modifications des boutons radio
    // Quand on sélectionne une frequence dans les radioButtons, on recharge la page en envoyant la location, division, frequence
    // cela na nous faire naviguer vers le SubscriptionController de la division choisie
    if ($('#frequencyRadioBox').is('div')) {
        $('#frequencyRadioBox').change(function () {
            var frequency = $("input[name='frequencyRadio']:checked").val();
            var url = $(this).data('url').replace('frequencyTmp', frequency);
            $(location).attr('href', url);
        });
    }

    if ($('#divisionType').is('input') || $('#divisionSelect').is('select')) {
        colorBodyFromDivision();
    }

    $('.infoproduct__esti').on('click', function () {
        Swal.fire({
            type: 'info',
            text: "Des infos pour estimer votre volume de déchets"
        })
    });

    /**
     * On adapte la taille de infoproduct à la taille de infoproduct-container
     */
    if ($('.infoproduct').is('div')) {
        $('.infoproduct-container').outerHeight($('.infoproduct').outerHeight());
    }

    /**
     * Lorsque l'utilisateur choisit des fichiers à mettre en PJ, on les affiche au dessus du bouton d'import
     */
    var options = {
        year: "numeric", month: "numeric", day: "numeric",
        hour: "numeric", minute: "numeric", second: "numeric",
        hour12: false
    };
    $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        var html = "";
        for (var i = 0; i < this.files.length; i++) {
            var lastModified = new Date(this.files[i].lastModified);
            html += "<span class='paprec-file'>" + this.files[i].name + " " + new Intl.DateTimeFormat('en-GB', options).format(lastModified) + "<br></span>";
            $('#listFiles').html(html);
        }
    });
    $('#paprec_commercialbundle_quoteRequest_attachedFiles').on('change', function () {
        var html = "";

        for (var i = 0; i < this.files.length; i++) {
            var lastModified = new Date(this.files[i].lastModified);
            html += "<span class='paprec-file'>" + this.files[i].name + " " + new Intl.DateTimeFormat('en-GB', options).format(lastModified) + "<br></span>";
            $('#listFiles').html(html);
        }
    });

    /*******************************************************************************************************************
     * REGULAR FORM
     */
    if ($('.request-writing-need-form').is('div')) {

        // Au submit du formulaire, on rajoute les attributs au <form> avant de POST
        $('#requestWritingNeedFormSubmitButton').on('click', function () {
            var form = $('form');
            form.attr('action', '#');
            form.attr('method', 'post');
            form.attr('enctype', 'multipart/form-data');
            form.submit();
        });
    }
    if ($('.request-writing-contact-details-form').is('div')) {
        $('#requestWritingContactDetailsFormSubmitButton').on('click', function () {
            $('form').submit();
        })
    }

    /*******************************************************************************************************************
     * CONTACT FORM
     */
    if ($('.contact-form').is('div')) {
        $('#contactFormSubmitButton').on('click', function () {
            $('#contactForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     var html = "";
        //     for (var i = 0; i <  $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').files.length; i++) {
        //         var lastModified = new Date( $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').files[i].lastModified);
        //         html +=  $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        // });
    }

    /*******************************************************************************************************************
     * CONTACT FROM CART FORM
     */
    if ($('.contact-from-cart-form').is('div')) {
        $('#contactFormSubmitButton').on('click', function () {
            $('#contactForm').submit();
        });

        reloadCart(true);

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     html = "";
        //
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });
    }

    /*******************************************************************************************************************
     * CALLBACK FORM
     */
    if ($('.callBack-form').is('div')) {

        reloadCart(true);

        /**
         * Gestion des datepickers
         */
            // On ne peut choisir une date de rappel qu'à partir d'aujourd'hui
        var now = new Date();

        // On définit arbitrairement la date maximum pour le rappel à dans 3 mois
        var maxDate = moment(now);
        maxDate.add(3, 'months');

        $('#paprec_commercialbundle_callBack_dateCallBack').datepicker({
            option: $.datepicker.regional["fr"],
            minDate: +1,
            maxDate: "+1M",
        });

        $('#paprec_commercialbundle_callBack_timeCallBack').timepicker({
            timeFormat: 'H:mm',
            minTime: '9',
            maxTime: '19',
            interval: 60,
            scrollbar: true,
            dynamic: false
        });


        $('#callBackFormSubmitButton').on('click', function () {
            // avant de submit, on convertit la date au format yyyy-mm-dd
            $('#paprec_commercialbundle_callBack_dateCallBack').val($('#paprec_commercialbundle_callBack_dateCallBack').val().split('/').reverse().join('-'));
            $('#callBackForm').submit();
        });
    }

    /*******************************************************************************************************************
     * COLLECTIVITE FORM
     */
    if ($('.collectivite-form').is('div')) {
        var files = [];

        $('#groupFormSubmitButton').on('click', function () {
            $('#regularForm').submit();
        });
    }

    /*******************************************************************************************************************
     * GROUPE & RESEAUX FORM
     */
    if ($('.groupe-reseau-form').is('div')) {

        $('#groupFormSubmitButton').on('click', function () {
            $('#regularForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     html = "";
        //
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });
    }

    /*******************************************************************************************************************
     * PARTICULIER FORM
     */
    if ($('.particulier-form').is('div')) {
        $('#groupFormSubmitButton').on('click', function () {
            $('#regularForm').submit();
        });

        // $('#paprec_commercialbundle_quoteRequestNonCorporate_attachedFiles').on('change', function () {
        //     html = "";
        //
        //     for (var i = 0; i < this.files.length; i++) {
        //         var lastModified = new Date(this.files[i].lastModified);
        //         html += this.files[i].name + " " + new Intl.DateTimeFormat('en-GB').format(lastModified) + " <a href=\"#\">x</a><br>";
        //         $('#listFiles').html(html);
        //     }
        //
        // });
    }

    /*******************************************************************************************************************
     * NEED FORM
     */
    if ($('.need-form').is('div')) {
        reloadCart();

        // Au clic sur un produit, on l'ajoute ou on le supprime des displayedCategories
        $('.productCheckboxPicto').click(function () {
            var url = $(this).data('url');
            $(location).attr('href', url);
        });

        // Au clic sur la croix de l'infoproduct, on supprime le produit des displayedProduct
        $('.infoproduct__close').click(function () {
            var url = $(this).data('url');
            $(location).attr('href', url);
        });

    }

    /*******************************************************************************************************************
     * DI OU CHANTIER NEED FORM
     */
    if ($('.di-need-form').is('div') || $('.chantier-need-form').is('div')) {
        // Au clic sur une catégorie, on l'ajoute où on la supprime des displayedCategories du cart
        $('.categoryCheckboxPicto').click(function () {
            var url = $(this).data('url');
            $(location).attr('href', url);
        });

        // Au clic sur un wrapper, on l'affiche ou on le cache
        $('.step__wrappertop').click(function (e) {
            var idWrapper = this.id.replace('step__wrappertop_', '');
            if ($(this).hasClass('open')) {
                $(this).removeClass('open');
                $('#productsWrapper_' + idWrapper).fadeOut('fast');
            } else {
                $(this).addClass('open');
                $('#productsWrapper_' + idWrapper).fadeIn('fast');
            }
        });

        /**
         * On intercepte le clic sur le bouton "Ajouter au panier" pour récupérer le produit et la catégorie et l'ajouter au cart
         */
        $('.addToCartSubmitButton').click(function () {
            var url = $(this).data('url');

            var productCategory = (this.id).replace('addToCartSubmitButton_', '').split('_', 2);
            var productId = productCategory[0];
            var categoryId = productCategory[1];
            var qtty = $('#quantityProducSelect_' + productId + '_' + categoryId).val();
            url = url.replace('quantityTmp', qtty);
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    // Quand on ajoute un produit au devis, on referme l'affichage des infos du produit ajouté
                    removeBadge(productId, categoryId);
                    $('#productCheckboxPicto_' + productId + '_' + categoryId).prepend("<span class=\"number\">" + qtty + "<span");
                    $('#validateNeedButton').removeAttr('disabled');
                    reloadCart();
                }
            });
        });

        $('.addToCartPackageSubmitButton').click(function () {
            var url = $(this).data('url');

            var productId = (this.id).replace('addToCartPackageSubmitButton', '');
            var qtty = $('#quantityProducSelect_' + productId).val();
            url = url.replace('quantityTmp', qtty);
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    // Quand on ajoute un produit au devis, on referme l'affichage des infos du produit ajouté
                    removeBadgeNoCat(productId);
                    $('#productCheckboxPicto_' + productId).prepend("<span class=\"number\">" + qtty + "<span");
                    $('#validateNeedButton').removeAttr('disabled');
                    reloadCart();
                }
            })
        });


    }

    /*******************************************************************************************************************
     * D3E OU CHANTIER PACKGE NEED FORM
     */
    if ($('.d3e-need-form').is('div') || $('.chantier-need-form').is('div')) {
        /**
         * Ajout un seul produit au clic sur le +
         */
        $('.addOneToCartPackageButton').click(function () {
            var url = $(this).data('url');

            var productId = (this.id).replace('addOneToCartPackageButton', '');
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    var prevQtty = removeBadgeNoCat(productId);
                    $('#productCheckboxPicto_' + productId).prepend("<span class=\"number\">" + (parseInt(prevQtty) + 1) + "<span");
                    $('#validateNeedButton').removeAttr('disabled');
                    reloadCart();
                }
            })
        });

        /**
         * Enlève un seul produit au clic sur le -
         */
        $('.removeOneToCartPackageButton').click(function () {
            var url = $(this).data('url');

            var productId = (this.id).replace('removeOneToCartPackageButton', '');
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    var prevQtty = removeBadgeNoCat(productId);
                    if (prevQtty > 1) {
                        $('#productCheckboxPicto_' + productId).prepend("<span class=\"number\">" + (parseInt(prevQtty) - 1) + "<span");
                    }
                    reloadCart();
                }
            })
        });
    }


    /*******************************************************************************************************************
     * D3E NEED FORM
     */
    if ($('.d3e-need-form').is('div')) {

        $('.addToCartSubmitButton').click(function () {
                var url = $(this).data('url');
                const productId = $(this).data('id');

                var data = [];
                const types = $('.infoproduct__type');
                types.each(function () {

                    var productD3EType = {
                        typeId: $(this).find($('.infoproduct__typeSelect')).val(),
                        qtty: $(this).find($('.infoproduct__qty')).val(),
                        optHandling: $(this).find($('.infoproduct__type__checkbox__optHandlingProductSelect')).prop('checked') ? 1 : 0,
                        optSerialNumberStmt: $(this).find($('.infoproduct__type__checkbox__optSerialNumberStmtProductSelect')).prop('checked') ? 1 : 0,
                        optDestruction: $(this).find($('.infoproduct__type__checkbox__optDestructionProductSelect')).prop('checked') ? 1 : 0,
                        productId: productId
                    };
                    data.push(productD3EType);
                });
                const request = JSON.stringify(data);

                $.ajax({
                    type: "POST",
                    url: url,
                    data: request,
                    success: function (response) {
                        // Quand on ajoute un produit au devis, on referme l'affichage des infos du produit ajouté
                        removeBadge(productId);
                        $('#productCheckboxPicto_' + productId).prepend("<span class=\"number\">1<span");
                        $('#validateNeedButton').removeAttr('disabled');
                        reloadCart()
                    }
                });
            }
        );

        $('.addToCartPackageSubmitButton').click(function () {
            var url = $(this).data('url');

            var productId = (this.id).replace('addToCartPackageSubmitButton', '');
            var qtty = $('#quantityProducSelect_' + productId).val();
            url = url.replace('quantityTmp', qtty);
            $.ajax({
                type: "POST",
                url: url,
                success: function (response) {
                    // Quand on ajoute un produit au devis, on referme l'affichage des infos du produit ajouté
                    removeBadgeNoCat(productId);
                    $('#productCheckboxPicto_' + productId).prepend("<span class=\"number\">" + qtty + "<span");
                    $('#validateNeedButton').removeAttr('disabled');
                    reloadCart();
                }
            })
        })

        $('.addNewTypeButton').click(function () {
            $('.infoproduct__type').first().clone().hide().insertAfter($('.infoproduct__type').last()).fadeIn(500);

            // On reset le formulaire
            $('.infoproduct__type').last().find('.infoproduct__qty').val('');
            $('.infoproduct__type').last().find($('#optHandlingProductSelect')).prop('checked', false);
            $('.infoproduct__type').last().find($('#optSerialNumberStmtProductSelect')).prop('checked', false);
            $('.infoproduct__type').last().find($('#optDestructionProductSelect')).prop('checked', false);
            $('.infoproduct-container').outerHeight($('.infoproduct').outerHeight());
        })
    }

    /*******************************************************************************************************************
     * CONTACT DETAILS FORM
     */
    if ($('.contact-details-form').is('div')) {
        reloadCart();
        initializeAutocompleteAddress();

        $('#contactDetailsFormSubmitButton').on('click', function () {
            const div = $('#divisionType').val();

            const address = $('input[id*=_address]').val();
            const cp = $('input[id*=_postalCode]').val();
            const city = $('input[id*=_city]').val();

            if ($('#headofficeAddressCheckbox').is('input') && !$('#headofficeAddressCheckbox').prop('checked')) {
                $('input[id*=_headofficeAddress]').val(address);
                $('input[id*=_headofficePostalCode]').val(cp);
                $('input[id*=_headofficeCity]').val(city);
            }
            if ($('#invoicingAddressCheckbox').is('input') && !$('#invoicingAddressCheckbox').prop('checked')) {
                $('input[id*=_invoicingAddress]').val(address);
                $('input[id*=_invoicingPostalCode]').val(cp);
                $('input[id*=_invoicingCity]').val(city);
            }

            $('#contactDetailsForm').submit();
        });

        $('#headofficeAddressCheckbox').on('change', function () {
            initializeAutocompleteHeadoffice();
            $('#headofficeAddressContainer').toggleClass('active');
        });

        $('#invoicingAddressCheckbox').on('change', function () {
            initializeAutocompleteInvoicing();
            $('#invoicingAddressContainer').toggleClass('active');
        });


    }


    /*******************************************************************************************************************
     * CONFIRM
     */
    if ($('.confirm').is('div')) {
        reloadCart();
    }


    /*******************************************************************************************************************
     * CHANTIER ET D3E DELIVERY FORM
     */
    if ($('.delivery-form').is('div')) {
        reloadCart(true);

        /**
         * Gestion des datepickers
         */
        var now = new Date();
        var minDate = moment(now);
        minDate.add(2, "days");
        // On définit arbitrairement la date maximum pour le rappel à dans 3 mois
        maxDate = moment(now);
        maxDate.add(3, 'months');
        /**
         * CHANTIER
         */
        if ($('.chantier-delivery-form').is('div')) {
            var installationDate = $('#paprec_commercialbundle_productchantierorderdelivery_installationDate').datepicker({
                option: $.datepicker.regional["fr"],
                minDate: +2,
                maxDate: "+2M",
            }).on("change", function () {
                removalDate.datepicker("option", "minDate", getDate(this));
            });

            var removalDate = $('#paprec_commercialbundle_productchantierorderdelivery_removalDate').datepicker({
                option: $.datepicker.regional["fr"],
                minDate: +2,
                maxDate: "+2M"
            }).on("change", function () {
                installationDate.datepicker("option", "maxDate", getDate(this));
            });


            $('#deliveryFormSubmitButton').on('click', function () {
                // avant de submit, on convertit la date au format yyyy-mm-dd
                $('#paprec_commercialbundle_productchantierorderdelivery_installationDate').val($('#paprec_commercialbundle_productchantierorderdelivery_installationDate').val().split('/').reverse().join('-'));
                $('#paprec_commercialbundle_productchantierorderdelivery_removalDate').val($('#paprec_commercialbundle_productchantierorderdelivery_removalDate').val().split('/').reverse().join('-'));

                $('#deliveryForm').submit();
            });
        }
        /**
         * D3E
         */
        if ($('.d3e-delivery-form').is('div')) {
            var installationDate = $('#paprec_commercialbundle_productd3eorderdelivery_installationDate').datepicker({
                option: $.datepicker.regional["fr"],
                minDate: +2,
                maxDate: "+2M",
            }).on("change", function () {
                removalDate.datepicker("option", "minDate", getDate(this));
            });

            var removalDate = $('#paprec_commercialbundle_productd3eorderdelivery_removalDate').datepicker({
                option: $.datepicker.regional["fr"],
                minDate: +2,
                maxDate: "+2M"
            }).on("change", function () {
                installationDate.datepicker("option", "maxDate", getDate(this));
            });

            $('#deliveryFormSubmitButton').on('click', function () {
                $('#paprec_commercialbundle_productd3eorderdelivery_installationDate').val($('#paprec_commercialbundle_productd3eorderdelivery_installationDate').val().split('/').reverse().join('-'));
                $('#paprec_commercialbundle_productd3eorderdelivery_removalDate').val($('#paprec_commercialbundle_productd3eorderdelivery_removalDate').val().split('/').reverse().join('-'));
                $('#deliveryForm').submit();
            });
        }
    }

    /*******************************************************************************************************************
     *PAYMENT FORM
     */
    if ($('.payment-form').is('div')) {
        reloadCart(true);
    }

})
;

/****************************************************************
 * FUNCTIONS
 ***************************************************************/

/**
 * Récupération de la date d'un datepicker jquery-ui
 * @param element
 * @returns {*|*|null}
 */
function getDate(element) {
    var date;
    try {
        date = $.datepicker.parseDate("dd/mm/yy", element.value);
    } catch (error) {
        date = null;
    }
    return date;
}

/**
 * Récupération du nombre d'agences proches
 */
function reloadNearbyAgencies() {
    if ($('.step__agency').is('div')) {
        var url = $('.step__agency').data('url');
        $.ajax({
            type: "GET",
            url: url,
            contentType: "html",
            success: function (response) {
                // On récupère l'HTML des agences proches et on l'insère dans step__agency dans la sidebar
                var htmlToDisplay = response.trim();
                $(".step__agency").html(htmlToDisplay);
            }
        });
    }
}

/**
 * Rechargement de l'HTML du Cart
 */
function reloadCart(readonly) {
    var url = $('#loadedCartPanel').data('url');
    var division = $('#loadedCartPanel').data('division');
    $.ajax({
        type: "GET",
        url: url,
        contentType: "html",
        success: function (response) {
            // On récupère l'HTML du cart dans "Mon besoin" et on l'insère dans loadedCartPanel dans la sidebar
            var htmlToDisplay = response.trim();
            $("#loadedCartPanel").html(htmlToDisplay);
            if (readonly) {
                //on supprime les croix dans le panier
                $('.buttonDeleteProduct').remove();
            } else {
                // On ajoute un listener sur les "x" dans la liste des produits dans le Cart
                $(".buttonDeleteProduct").click(function () {
                    var urlRemove = $(this).data('url');
                    if (division === 'D3E') {
                        let productId = (this.id).replace('buttonDeleteProduct_', '');
                        $.ajax({
                            type: "GET",
                            dataType: "json",
                            url: urlRemove.replace('productTmp', productId),
                            success: function (response) {
                                removeBadgeNoCat(productId);
                                reloadCart();
                            }
                        });
                    } else {
                        productCategory = (this.id).replace('buttonDeleteProduct_', '').split('_', 2);
                        productId = productCategory[0];
                        categoryId = productCategory[1];
                        $.ajax({
                            type: "GET",
                            dataType: "json",
                            url: urlRemove
                                .replace('categoryTmp', categoryId)
                                .replace('productTmp', productId),
                            success: function (response) {
                                removeBadge(productId, categoryId);
                                removeBadgeNoCat(productId);
                                reloadCart();
                            }
                        });
                    }
                })
            }
        }
    });
}

/**
 * Supprime le badge au dessus d'un produit indiquant la quantité de ce produit ajoutée au panier
 * @param productId
 * @param categoryId
 */
function removeBadge(productId, categoryId) {
    $('#productCheckboxPicto_' + productId + '_' + categoryId).find('span.number').remove();
}

/**
 * POUR D3E
 * Supprime le badge au dessus d'un produit indiquant la quantité de ce produit ajoutée au panier
 * @param productId
 */
function removeBadgeNoCat(productId) {
    var prevQtty = $('#productCheckboxPicto_' + productId).find('span.number').html();
    if (typeof prevQtty === 'undefined' || prevQtty.length < 1) {
        prevQtty = 0;
    }
    $('#productCheckboxPicto_' + productId).find('span.number').remove();
    return prevQtty;
}

/**
 * Initialise l'autocomplete Google sur l'input ayant pour id = "id"
 * si nonCorporate = true, on souhaite récupérer uniquement le codePostal de l'autocomplete Google
 */
function initializeAutocomplete(id, nonCorporate = false) {
    var element = document.getElementById(id);
    var options = {
        types: ['(regions)'],
        componentRestrictions: {country: "fr"}
    };
    if (element) {
        var autocomplete = new google.maps.places.Autocomplete(element, options);
        if (nonCorporate) {
            google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChangedNonCorporate);
        } else {
            google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChanged);
        }
    }
}

/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google
 */
function onPlaceChanged() {
    var loc = $('#locationSelect').val();
    var place = this.getPlace();
    // On init les variables car si un champ manque, on se retrouve avec city/%20/long dans l'url et non city//long
    var city = " ";
    var postalCode = " ";

    var lat = place.geometry.location.lat();
    var long = place.geometry.location.lng();
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                postalCode = component.long_name;
            }
            if (component.types[j] === 'locality') {
                city = component.long_name;
            }
        }
    }
    var url = $('#locationSelect').data('url')
        .replace('locationTmp', loc)
        .replace('cityTmp', city)
        .replace('postalCodeTmp', postalCode)
        .replace('longTmp', long)
        .replace('latTmp', lat);
    $(location).attr('href', url);
}


/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google pour un formulaire nonCorporate (Groupe & Réseaux, Collectivité, Particulier)
 */
function onPlaceChangedNonCorporate() {
    var place = this.getPlace()
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                $('#paprec_commercialbundle_quoteRequestNonCorporate_postalCode').val(component.long_name);
            }
        }
    }
}

/**
 * On récupère la valeur du champ divisionSelect et on applique au body la couleur de la division choisie
 */
function colorBodyFromDivision() {
    var division = '';
    if ($('#divisionSelect').is('select')) {
        division = $('#divisionSelect').val();
    } else if ($('#divisionType').is('input')) {
        division = $("#divisionType").val();
    }
    if (division !== '') {
        if (division === 'DI') {
            $('body').addClass('tunnel--green');
            if ($('.tunnel-package').is('div')) {
                $('body').addClass('tunnel--green--package');
            }
        } else if (division === 'CHANTIER') {
            $('body').addClass('tunnel--orange');
            if ($('.tunnel-package').is('div')) {
                $('body').addClass('tunnel--orange--package');
            }
        } else if (division === 'D3E') {
            $('body').addClass('tunnel--purple');
            if ($('.tunnel-package').is('div')) {
                $('body').addClass('tunnel--purple--package');
            }
        }
    }
}

/****************************************************************
 * CONTACT DETAILS ADDRESSES
 ***************************************************************/

/**
 * Initialise l'autocomplete Google sur l'adresse
 */
function initializeAutocompleteAddress() {
    var element = $('input[id*=_address]')[0];
    var options = {
        componentRestrictions: {country: "fr"}
    };
    if (element) {
        var autocomplete = new google.maps.places.Autocomplete(element, options);
        google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChangedAddress);
    }
}

/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google
 */
function onPlaceChangedAddress() {
    var place = this.getPlace();
    var address = '';
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                $('input[id*=_postalCode]').val(component.long_name);
            }
            if (component.types[j] === 'locality') {
                $('input[id*=_city]').val(component.long_name);
            }
            if (component.types[j] === 'street_number') {
                address += component.long_name + ' ';
            }
            if (component.types[j] === 'route') {
                address += component.long_name;
            }
        }
    }
    $('input[id*=_address]').val(address);
}

/**
 * Initialise l'autocomplete Google sur l'adresse headoffice
 */
function initializeAutocompleteHeadoffice() {
    var element = $('input[id*=_headofficeAddress]')[0];
    var options = {
        componentRestrictions: {country: "fr"}
    };
    if (element) {
        var autocomplete = new google.maps.places.Autocomplete(element, options);
        google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChangedHeadoffice);
    }
}

/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google
 */
function onPlaceChangedHeadoffice() {
    var place = this.getPlace();
    var address = '';
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                $('input[id*=_headofficePostalCode]').val(component.long_name);
            }
            if (component.types[j] === 'locality') {
                $('input[id*=_headofficeCity]').val(component.long_name);
            }
            if (component.types[j] === 'street_number') {
                address += component.long_name + ' ';
            }
            if (component.types[j] === 'route') {
                address += component.long_name;
            }
        }
    }
    $('input[id*=_headofficeAddress]').val(address);
}

/**
 * Initialise l'autocomplete Google sur l'adresseInvoicing
 */
function initializeAutocompleteInvoicing() {
    var element = $('input[id*=_invoicingAddress]')[0];
    var options = {
        componentRestrictions: {country: "fr"}
    };
    if (element) {
        var autocomplete = new google.maps.places.Autocomplete(element, options);
        google.maps.event.addListener(autocomplete, 'place_changed', onPlaceChangedInvoicing);
    }
}

/**
 * Fonction appelée lorsque l'on choisit une proposition de l'autocomplete Google
 */
function onPlaceChangedInvoicing() {
    var place = this.getPlace();
    var address = '';
    for (var i in place.address_components) {
        var component = place.address_components[i];
        for (var j in component.types) {  // Some types are ["country", "political"]
            if (component.types[j] === 'postal_code') {
                $('input[id*=_invoicingPostalCode]').val(component.long_name);
            }
            if (component.types[j] === 'locality') {
                $('input[id*=_invoicingCity]').val(component.long_name);
            }
            if (component.types[j] === 'street_number') {
                address += component.long_name + ' ';
            }
            if (component.types[j] === 'route') {
                address += component.long_name;
            }
        }
    }
    $('input[id*=_invoicingAddress]').val(address);
}




