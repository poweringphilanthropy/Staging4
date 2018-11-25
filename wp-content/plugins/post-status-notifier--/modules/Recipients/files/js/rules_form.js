jQuery(document).ready( function($) {

    var textarea = $('textarea[name="to_dyn"]');
    textarea.hide();
    textarea.closest('li').append('<div id="to_dyn_editor"></div>');

    var editorFilters = ace.edit("to_dyn_editor");

    editorFilters.setTheme("ace/theme/github");
    editorFilters.getSession().setMode("ace/mode/twig");
    editorFilters.getSession().setValue(textarea.val());

    editorFilters.getSession().on('change', function() {
        textarea.val(editorFilters.getSession().getValue());
    });
});