// jQuery(document).ready(function ($) {
//     $("#add-row").click(function () {
//         var $table = $("#charitable-campaign-volunteers-need tbody"),

//             index = function () {
//                 var $rows = $table.find("[data-index]"),
//                     index = 0;

//                 if ($rows.length)
//                     index = parseInt($rows.last().data("index"), 10) + 1;

//                 return index;

//             }(),

//             row = '<tr data-index="' + index + '">'
//                 + '<td><input type="text" id="campaign_suggested_donations_' + index + '" name="volunteers[' + index + '][need]" placeholder="" style="width:100%;"/>'
//                 + '</tr>';

//         $table.find(".no-suggested-amounts").hide();
//         $table.append(row);

//     });

// });

