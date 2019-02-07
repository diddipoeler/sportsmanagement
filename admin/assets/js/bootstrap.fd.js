/**
 * @source: https://github.com/Saluev/bootstrap-file-dialog/blob/master/bootstrap.fd.js
 * Copyright (C) 2014-2015 Tigran Saluev
 */
(function($) {
"use strict";

$.FileDialog = function FileDialog(userOptions) {
    var options = $.extend($.FileDialog.defaults, userOptions),
        modal = $([
            "<div class='modal fade'>",
            "    <div class='modal-dialog'>",
            "        <div class='modal-content'>",
            "            <div class='modal-header'>",
            "                <button type='button' class='close' data-dismiss='modal'>",
            "                    <span aria-hidden='true'>&times;</span>",
            "                    <span class='sr-only'>",
                                     options.cancel_button,
            "                    </span>",
            "                </button>",
            "                <h4 class='modal-title'>",
                                options.title,
            "                </h4>",
            "            </div>",
            "            <div class='modal-body'>",
            "                <input type='file' />",
            "                <div class='bfd-dropfield'>",
            "                    <div class='bfd-dropfield-inner'>",
                                     options.drag_message,
            "                    </div>",
            "                </div>",
            "                <div class='container-fluid bfd-files'>",
            "                </div>",
            "            </div>",
            "            <div class='modal-footer'>",
            "                <button type='button' class='btn btn-primary bfd-ok'>",
                                 options.ok_button,
            "                </button>",
            "                <button type='button' class='btn btn-default bfd-cancel'",
            "                                data-dismiss='modal'>",
                                 options.cancel_button,
            "                </button>",
            "            </div>",
            "        </div>",
            "    </div>",
            "</div>"
        ].join("")),
        done = false,
        input = $("input:file", modal),
        dropfield = $(".bfd-dropfield", modal),
        dropfieldInner = $(".bfd-dropfield-inner", dropfield);

    dropfieldInner.css({
        "height": options.dropheight,
        "padding-top": options.dropheight / 2 - 32
    });

    input.attr({
        "accept": options.accept,
        "multiple": options.multiple
    });

    dropfield.on("click.bfd", function() {
        input.trigger("click");
    });

    var loadedFiles = [],
        readers = [];

    var loadFile = function(f) {
        var reader = new FileReader(),
            progressBar,
            row;

        readers.push(reader);

        reader.onloadstart = function() {

        };

        reader.onerror = function(e) {
            if(e.target.error.code === e.target.error.ABORT_ERR) {
                return;
            }
            progressBar.parent().html([
                "<div class='bg-danger bfd-error-message'>",
                    options.error_message,
                "</div>"
            ].join("\n"));
        };

        reader.onprogress = function(e) {
            var percentLoaded = Math.round(e.loaded * 100 / e.total) + "%";
            progressBar.attr("aria-valuenow", e.loaded);
            progressBar.css ("width", percentLoaded);
            $(".sr-only", progressBar).text(percentLoaded);
        };

        reader.onload = function(e) {
            f.content = e.target.result;
            loadedFiles.push(f);
            progressBar.removeClass("active");
        };

        var progress = $([
            "<div class='col-xs-7 col-sm-4 bfd-info'>",
            "    <span class='glyphicon glyphicon-remove bfd-remove'></span>&nbsp;",
            "    <span class='glyphicon glyphicon-file'></span>&nbsp;" + f.name,
            "</div>",
            "<div class='col-xs-5 col-sm-8 bfd-progress'>",
            "    <div class='progress'>",
            "        <div class='progress-bar progress-bar-striped active' role='progressbar'",
            "            aria-valuenow='0' aria-valuemin='0' aria-valuemax='" + f.size + "'>",
            "            <span class='sr-only'>0%</span>",
            "        </div>",
            "    </div>",
            "</div>"
        ].join(""));
        progressBar = $(".progress-bar", progress);
        $(".bfd-remove", progress).tooltip({
            "container": "body",
            "html": true,
            "placement": "top",
            "title": options.remove_message
        }).on("click.bfd", function() {
            var idx = loadedFiles.indexOf(f);
            if(idx >= 0) {
                loadedFiles.pop(idx);
            }
            row.fadeOut();
            try { reader.abort(); } catch(e) { }
        });
        row = $("<div class='row'></div>");
        row.append(progress);
        $(".bfd-files", modal).append(row);

        reader["readAs" + options.readAs](f);
    };

    var loadFiles = function loadFiles(flist) {
        Array.prototype.forEach.apply(flist, [loadFile]);
    };

    // setting up event handlers
    input.change(function(e) {
        e = e.originalEvent;
        var files = e.target.files;
        loadFiles(files);
        // clearing input field by replacing it with a clone (lol)
        var newInput = input.clone(true);
        input.replaceWith(newInput);
        input = newInput;
    });
    // // drag&drop stuff
    dropfield.on("dragenter.bfd", function() {
        dropfieldInner.addClass("bfd-dragover");
    }).on("dragover.bfd", function(e) {
        e = e.originalEvent;
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";
    }).on("dragleave.bfd drop.bfd", function() {
        dropfieldInner.removeClass("bfd-dragover");
    }).on("drop.bfd", function(e) {
        e = e.originalEvent;
        e.stopPropagation();
        e.preventDefault();
        var files = e.dataTransfer.files;
        if(files.length === 0) {
            // problem with desktop/browser
            // ... TODO
        }
        loadFiles(files);
    });

    $(".bfd-ok", modal).on("click.bfd", function() {
        var event = $.Event("files.bs.filedialog");
        event.files = loadedFiles;
        modal.trigger(event);
        done = true;
        modal.modal("hide");
    });

    modal.on("hidden.bs.modal", function() {
        readers.forEach(function(reader) { try { reader.abort(); } catch(e) { } });
        if(!done) {
            var event = $.Event("cancel.bs.filedialog");
            modal.trigger(event);
        }
        modal.remove();
    });

    $(document.body).append(modal);
    modal.modal();

    return modal;
};

$.FileDialog.defaults = {
    "accept": "*", /* e.g. 'image/*' */
    "cancel_button": "Close",
    "drag_message": "Drop files here",
    "dropheight": 400,
    "error_message": "An error occured while loading file",
    "multiple": true,
    "ok_button": "OK",
    "readAs": "DataURL", /* possible choices: BinaryString, Text, DataURL, ArrayBuffer, */
    "remove_message": "Remove&nbsp;file",
    "title": "Load file(s)"
};

})(jQuery);
