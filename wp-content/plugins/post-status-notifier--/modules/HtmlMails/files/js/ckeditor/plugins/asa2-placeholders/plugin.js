CKEDITOR.plugins.add('asa2-placeholders', {
    requires : ['richcombo'],
    init : function( editor )
    {
        var strings = [];
        strings.push(['{{ AmazonURL }}', '{{ AmazonURL }}', 'Alias for DetailPageURL']);
        strings.push(['{{ Author }}', '{{ Author }}', '']);
        strings.push(['{{ Binding }}', '{{ Binding }}', '']);
        strings.push(['{{ Brand }}', '{{ Brand }}', 'The item\'s brand name']);
        strings.push(['{{ CountryCode }}', '{{ CountryCode }}', 'The country code of the Amazon store']);
        strings.push(['{{ CustomerReviewsAverageRating }}', '{{ CustomerReviewsAverageRating }}', 'The average rating of all customer reviews']);
        strings.push(['{{ CustomerReviewsExist }}', '{{ CustomerReviewsExist }}', 'If customers ratings exist']);
        strings.push(['{{ CustomerReviewsImgSrc }}', '{{ CustomerReviewsImgSrc }}', 'The URL of the reviews stars image']);
        strings.push(['{{ CustomerReviewsImgTag }}', '{{ CustomerReviewsImgTag }}', 'The reviews stars image URL embedded in a HTML img tag']);
        strings.push(['{{ CustomerReviewsTotal }}', '{{ CustomerReviewsTotal }}', 'Total number of customer reviews']);
        strings.push(['{{ CustomerReviewsURL }}', '{{ CustomerReviewsURL }}', 'URL to the customer reviews page']);
        strings.push(['{{ DetailPageURL }}', '{{ DetailPageURL }}', 'Item page URL']);
        strings.push(['{{ EAN }}', '{{ EAN }}', '']);
        strings.push(['{{ Edition }}', '{{ Edition }}', '']);
        strings.push(['{{ EditorialReviews }}', '{{ EditorialReviews }}', '']);
        strings.push(['{{ EditorialReviewsContent }}', '{{ EditorialReviewsContent }}', '']);
        strings.push(['{{ EditorialReviewsIsLinkSuppressed }}', '{{ EditorialReviewsIsLinkSuppressed }}', '']);
        strings.push(['{{ EditorialReviewsSource }}', '{{ EditorialReviewsSource }}', '']);
        strings.push(['{{ Features }}', '{{ Features }}', 'The product features']);
        strings.push(['{{ FeaturesHTML }}', '{{ FeaturesHTML }}', '']);
        strings.push(['{{ ISBN }}', '{{ ISBN }}', '']);
        strings.push(['{{ ImageSets }}', '{{ ImageSets }}', '']);
        strings.push(['{{ ImageSetsTotal }}', '{{ ImageSetsTotal }}', 'The total number of image sets']);
        strings.push(['{{ IsPrime }}', '{{ IsPrime }}', 'If the product is available via Amazon Prime']);
        strings.push(['{{ ItemDimensionsHeight }}', '{{ ItemDimensionsHeight }}', '']);
        strings.push(['{{ ItemDimensionsLength }}', '{{ ItemDimensionsLength }}', '']);
        strings.push(['{{ ItemDimensionsWeight }}', '{{ ItemDimensionsWeight }}', '']);
        strings.push(['{{ ItemDimensionsWidth }}', '{{ ItemDimensionsWidth }}', '']);
        strings.push(['{{ ItemLinks }}', '{{ ItemLinks }}', '']);
        strings.push(['{{ Label }}', '{{ Label }}', '']);
        strings.push(['{{ Languages }}', '{{ Languages }}', '']);
        strings.push(['{{ LanguagesHTML }}', '{{ LanguagesHTML }}', '']);
        strings.push(['{{ LargeImageHeight }}', '{{ LargeImageHeight }}', '']);
        strings.push(['{{ LargeImageURL }}', '{{ LargeImageURL }}', '']);
        strings.push(['{{ LargeImageWidth }}', '{{ LargeImageWidth }}', '']);
        strings.push(['{{ ListPriceAmount }}', '{{ ListPriceAmount }}', 'The list price / catalog price. Just the amount without currency']);
        strings.push(['{{ ListPriceCurrencyCode }}', '{{ ListPriceCurrencyCode }}', 'The currency code of the list price like "EUR" or "$"']);
        strings.push(['{{ ListPriceFormattedPrice }}', '{{ ListPriceFormattedPrice }}', 'The formatted list price including amount and currency code like it is shown on the Amazon page']);
        strings.push(['{{ Manufacturer }}', '{{ Manufacturer }}', '']);
        strings.push(['{{ MediumImageHeight }}', '{{ MediumImageHeight }}', '']);
        strings.push(['{{ MediumImageURL }}', '{{ MediumImageURL }}', '']);
        strings.push(['{{ MediumImageWidth }}', '{{ MediumImageWidth }}', '']);
        strings.push(['{{ NumberOfPages }}', '{{ NumberOfPages }}', '']);
        strings.push(['{{ OfferAmountSaved }}', '{{ OfferAmountSaved }}', 'The amount saved compared to the list price. Without currency code']);
        strings.push(['{{ OfferAmountSavedCurrencyCode }}', '{{ OfferAmountSavedCurrencyCode }}', 'Just the currency code of the amount saved']);
        strings.push(['{{ OfferAmountSavedFormattedPrice }}', '{{ OfferAmountSavedFormattedPrice }}', 'The amount saved compared to the list price. Includes amount and currency code.']);
        strings.push(['{{ OfferAvailability }}', '{{ OfferAvailability }}', '']);
        strings.push(['{{ OfferPercentageSaved }}', '{{ OfferPercentageSaved }}', 'The percentage saved compared to the list price. Just the amount without percentage sign.']);
        strings.push(['{{ OffersFallbackPriceAmount }}', '{{ OffersFallbackPriceAmount }}', 'The fallback offer price amount without currency code. Will contain the main price or, if not applicable, the lowest new price.']);
        strings.push(['{{ OffersFallbackPriceCurrencyCode }}', '{{ OffersFallbackPriceCurrencyCode }}', 'The fallback offer price currency code']);
        strings.push(['{{ OffersFallbackPriceFormattedPrice }}', '{{ OffersFallbackPriceFormattedPrice }}', 'The fallback price including the amount and currency code']);
        strings.push(['{{ OffersLowestNewPriceAmount }}', '{{ OffersLowestNewPriceAmount }}', '']);
        strings.push(['{{ OffersLowestNewPriceCurrencyCode }}', '{{ OffersLowestNewPriceCurrencyCode }}', '']);
        strings.push(['{{ OffersLowestNewPriceFormattedPrice }}', '{{ OffersLowestNewPriceFormattedPrice }}', '']);
        strings.push(['{{ OffersLowestUsedPriceAmount }}', '{{ OffersLowestUsedPriceAmount }}', '']);
        strings.push(['{{ OffersLowestUsedPriceCurrencyCode }}', '{{ OffersLowestUsedPriceCurrencyCode }}', '']);
        strings.push(['{{ OffersLowestUsedPriceFormattedPrice }}', '{{ OffersLowestUsedPriceFormattedPrice }}', '']);
        strings.push(['{{ OffersMainPriceAmount }}', '{{ OffersMainPriceAmount }}', 'The main offer price amount without currency code']);
        strings.push(['{{ OffersMainPriceCurrencyCode }}', '{{ OffersMainPriceCurrencyCode }}', 'The main offer price currency code']);
        strings.push(['{{ OffersMainPriceFormattedPrice }}', '{{ OffersMainPriceFormattedPrice }}', 'The main offer price including the amount and currency code']);
        strings.push(['{{ OffersMoreOffersURL }}', '{{ OffersMoreOffersURL }}', '']);
        strings.push(['{{ OffersTotalCollectible }}', '{{ OffersTotalCollectible }}', '']);
        strings.push(['{{ OffersTotalNew }}', '{{ OffersTotalNew }}', '']);
        strings.push(['{{ OffersTotalOfferPages }}', '{{ OffersTotalOfferPages }}', '']);
        strings.push(['{{ OffersTotalOffers }}', '{{ OffersTotalOffers }}', '']);
        strings.push(['{{ OffersTotalRefurbished }}', '{{ OffersTotalRefurbished }}', '']);
        strings.push(['{{ OffersTotalUsed }}', '{{ OffersTotalUsed }}', '']);
        strings.push(['{{ OriginalTitle }}', '{{ OriginalTitle }}', 'Original Amazon product title']);
        strings.push(['{{ PackageDimensionsHeight }}', '{{ PackageDimensionsHeight }}', '']);
        strings.push(['{{ PackageDimensionsLength }}', '{{ PackageDimensionsLength }}', '']);
        strings.push(['{{ PackageDimensionsWeight }}', '{{ PackageDimensionsWeight }}', '']);
        strings.push(['{{ PackageDimensionsWidth }}', '{{ PackageDimensionsWidth }}', '']);
        strings.push(['{{ ParentASIN }}', '{{ ParentASIN }}', '']);
        strings.push(['{{ ProductGroup }}', '{{ ProductGroup }}', '']);
        strings.push(['{{ ProductTypeName }}', '{{ ProductTypeName }}', '']);
        strings.push(['{{ PublicationDate }}', '{{ PublicationDate }}', 'The item\'s publication date']);
        strings.push(['{{ PublicationDateBlogFormat }}', '{{ PublicationDateBlogFormat }}', 'The item\'s publication date in the blog\'s date format.']);
        strings.push(['{{ Publisher }}', '{{ Publisher }}', '']);
        strings.push(['{{ ReleaseDate }}', '{{ ReleaseDate }}', 'The item\'s release date']);
        strings.push(['{{ ReleaseDateBlogFormat }}', '{{ ReleaseDateBlogFormat }}', 'The item\'s release date in the blog\'s date format.']);
        strings.push(['{{ RepoTitle }}', '{{ RepoTitle }}', 'The product title of the repo item. May be different from the original title if you changed it.']);
        strings.push(['{{ RequestTimestamp }}', '{{ RequestTimestamp }}', 'The last refresh date']);
        strings.push(['{{ RequestTimestampBlogFormat }}', '{{ RequestTimestampBlogFormat }}', 'The last refresh date in blog time format']);
        strings.push(['{{ Size }}', '{{ Size }}', '']);
        strings.push(['{{ SmallImageHeight }}', '{{ SmallImageHeight }}', '']);
        strings.push(['{{ SmallImageURL }}', '{{ SmallImageURL }}', '']);
        strings.push(['{{ SmallImageWidth }}', '{{ SmallImageWidth }}', '']);
        strings.push(['{{ Studio }}', '{{ Studio }}', '']);
        strings.push(['{{ Title }}', '{{ Title }}', 'Item title. The Repo item title, if you customized it, otherwise the original Amazon product title.']);
        strings.push(['{{ repo_content }}', '{{ repo_content }}', 'The repo item\'s content, if loaded from a Repo item']);
        strings.push(['{{ repo_excerpt }}', '{{ repo_excerpt }}', 'The repo item\'s excerpt, if loaded from a Repo item']);

        editor.ui.addRichCombo('asa2-placeholders',
            {
                label: 'ASA 2',
                title: 'ASA 2 placeholders',
                voiceLabel: 'ASA2 placeholderst',
                className: 'cke_format',
                multiSelect:false,
                modes: { wysiwyg: 1, source: 0 },
                width: 400,
                panel:
                {
                    css: [ editor.config.contentsCss, CKEDITOR.skin.getPath('editor') ],
                    voiceLabel: editor.lang.panelVoiceLabel
                },

                init: function()
                {
                    this.startGroup( "Insert placeholder" );
                    for (var i in strings)
                    {
                        this.add(strings[i][0], strings[i][1], strings[i][2]);
                    }
                },

                onClick: function( value )
                {
                    console.log(editor);

                    editor.focus();
                    editor.fire( 'saveSnapshot' );

                    if (editor.mode == 'wysiwyg') {
                        editor.insertText(value);
                    } else {

                    }
                    editor.fire( 'saveSnapshot' );
                }
            });
    }
});
