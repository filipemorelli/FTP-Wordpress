$(document).ready(function ()
{

    var treeview = $("#treeview").kendoTreeView({
        //dragAndDrop: true,
        select: function (e)
        {
        },
        drag: function (e)
        {
            if ($(e.dropTarget).parents("li").hasClass('file'))
            {
                e.setStatusClass('k-i-cancel');
            }
        },
        drop: function (e)
        {
            if ($(e.destinationNode).hasClass('file'))
            {
                e.preventDefault();
            }
            else
            {
                if (e.dropPosition !== "over" && e.dropPosition !== "before")
                {
                    e.preventDefault();
                    return;
                }
                var oldPath = getPath(e.sourceNode);
                var newPath = getPath(e.destinationNode);
                if (oldPath !== newPath)
                {
                    actionsFile(oldPath, 'move', newPath);
                }
            }
        }
    });
    $("#context-menu-image").kendoContextMenu({
        target: "#treeview",
        filter: ".image",
        open: function (e)
        {
            var path = getPath(e.target);
        },
        select: function (e)
        {
            var path = getPath(e.target);
            var type = $(e.item).data('type');
            ajaxRequestAction(path, type, e);
        }
    });
    $("#context-menu-code").kendoContextMenu({
        target: "#treeview",
        filter: ".code",
        open: function (e)
        {
            var path = getPath(e.target);
        },
        select: function (e)
        {
            var path = getPath(e.target);
            var type = $(e.item).data('type');
            ajaxRequestAction(path, type, e);
        }
    });
    $("#context-menu-external").kendoContextMenu({
        target: "#treeview",
        filter: ".download",
        open: function (e)
        {
            var path = getPath(e.target);
        },
        select: function (e)
        {
            var path = getPath(e.target);
            var type = $(e.item).data('type');
            ajaxRequestAction(path, type, e);
        }
    });
    $("#context-menu-video").kendoContextMenu({
        target: "#treeview",
        filter: ".video",
        open: function (e)
        {
            var path = getPath(e.target);
        },
        select: function (e)
        {
            var path = getPath(e.target);
            var type = $(e.item).data('type');
            ajaxRequestAction(path, type, e);
        }
    });
    $("#context-menu-folder").kendoContextMenu({
        target: "#treeview",
        filter: ".dir",
        open: function (e)
        {
            var path = getPath(e.target);
        },
        select: function (e)
        {
            var path = getPath(e.target);
            var type = $(e.item).data('type');
            ajaxRequestAction(path, type, e);
        }
    });
    $(document).on('contextmenu', ".k-treeview", function (e)
    {
        e.preventDefault();
        $("#treeview_tv_active").contextmenu();
    });
    $("#employees-search").on("input", function ()
    {
        var query = $(this).val().toLowerCase();
        var id = "treeview";
        setTimeout(function ()
        {
            filter(query, this);
        }, 200);
    });
    $("#treeview li:not(.dir)").on('dblclick', function (e)
    {
        var path = getPath(e.target);
        var type = $(e.currentTarget).data('type');
        ajaxRequestAction(path, type, e);
    });
    function getPath(node)
    {
        var kitems = $(node).add($(node).parentsUntil('.k-treeview', '.k-item'));
        var texts = $.map(kitems, function (kitem)
        {
            return $(kitem).find('>div span.k-in').text();
        });
        texts = texts.join('/');
        if (texts.charAt(texts.length - 1) === "/")
        {
            return texts.substr(0, texts.length - 1);
        }
        return texts;
    }

    function filter(value, field)
    {
        var v = value.toLowerCase();
        try
        {
            $("#treeview li:not(:contains(" + v + ")):not(.context-menu li)").hide();
            $("#treeview li:contains(" + v + "):not(.context-menu li)").show();
        }
        catch (e)
        {
            kendo.alert("Não é possivel pesquisar esse termo");
            $(field).val("");
        }

    }

    function ajaxRequestAction(path, type, event)
    {
        if (path === undefined)
        {
            return;
        }

        if (type === "download")
        {
            window.location = "admin-ajax.php?action=nopriv_FTPWordpress_download_file&type=download&path=" + path;
            return;
        }
        else if (type === "delete")
        {
            kendo.confirm("Are you sure that you want to proceed?").then(function ()
            {
                doAjaxRequest(path, type, event);
            }, $.noop);
            return;
        }
        else if (type === "copy")
        {
            kendo.prompt("What path do you want to copy?", path).then(function (data)
            {
                actionsFile(path, 'copy', data);
            }, $.noop);
            return;
        }
        else if (type === "move")
        {
            kendo.prompt("What path do you want to move?", path).then(function (data)
            {
                actionsFile(path, 'move', data);
            }, $.noop);
            return;
        }
        else if (type === "rename")
        {
            kendo.prompt("What new file name?", event.target.innerText.trim()).then(function (data)
            {
                actionsFile(path, 'rename', data);
            }, $.noop);
            return;
        }
        else if (type === "folder")
        {
            kendo.prompt("What new folder name?", 'new folder').then(function (data)
            {
                actionsFile(path, 'folder', data);
            }, $.noop);
            return;
        }
        else if (type === "upload")
        {
            uploadAlert(path, type, event);
            return;
        }
        doAjaxRequest(path, type, event);
    }

    function uploadAlert(path, type, event)
    {
        var dialog = $('<div id="up-dialog"></div>');
        var inputFileContent = $('<div class="input-group"> <label class="input-group-btn"> <span class="btn btn-primary"> Browse&hellip; <input type="file" style="display: none;" multiple> </span> </label> <input type="text" class="form-control" readonly> </div>');
        inputFileContent.on('change', ':file', function ()
        {
            var inputFile = $(this);
            var numFiles = inputFile.get(0).files ? inputFile.get(0).files.length : 1;
            var label = inputFile.val().replace(/\\/g, '/').replace(/.*\//, '');
            var inputText = $(this).parents('.input-group').find(':text');
            var log = numFiles > 1 ? numFiles + ' files selected' : label;
            if (inputText.length)
            {
                inputText.val(log);
            }
            else
            {
                if (log)
                    alert(log);
            }

        });
        $("body").append(dialog);
        dialog.kendoDialog({
            width: "300px",
            title: "UploadFile",
            closable: true,
            modal: true,
            content: inputFileContent,
            actions: [
                {
                    text: 'Upload Now!',
                    primary: true,
                    action: function ()
                    {
                        doUploadFile(path, type, event, inputFileContent);
                    }
                },
                {text: 'cancel'}
            ],
            close: function ()
            {
                dialog.remove();
            }
        });
    }

    function doUploadFile(path, type, event, inputFileContent)
    {
        var files = inputFileContent.find(":file")[0].files;
        var data = new FormData();
        for (var i in files)
        {
            if (Number.isInteger(parseInt(i)))
            {
                data.append('file_' + i, files[i]);
            }
        }
        data.append('action', 'FTPWordpress');
        data.append('path', path);
        data.append('type', type);
        $.ajax({
            type: "POST",
            url: "admin-ajax.php",
            data: data,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (data)
            {
                if (data.status)
                {
                    kendo.confirm(data.msg).then(function (e)
                    {
                        blockScreen();
                        window.location.reload();
                    }, $.noop);
                }
                else
                {
                    kendo.alert(data.msg)
                }
            },
            error: function (data, textStatus, jqXHR)
            {
            }
        });
    }

    function doAjaxRequest(path, type, event)
    {
        $.ajax({
            url: 'admin-ajax.php',
            data: {action: 'FTPWordpress', path: path, type: type},
            type: 'POST',
            dataType: 'json',
            beforeSend: function ()
            {
                blockScreen();
            },
            success: function (data)
            {
                if (data.status)
                {
                    switch (type)
                    {
                        case 'properties':
                            propertiesFile(data);
                            break;
                        case 'editor':
                        case 'code':
                            codeEditor(data.info, path);
                            break;
                        case 'textarea':
                            codeTextarea(data.info, path);
                            break;
                        case 'delete':
                            deleteFile(data, event);
                            break;
                        case 'image':
                            openImage(data);
                            break;
                        case 'video':
                            openVideo(data);
                            break;
                        default:

                            break;
                    }
                }
            },
            complete: function ()
            {
                unblockScreen();
            },
            error: function (xhr, status, error)
            {
                kendo.alert("Server Error");
            }
        });
    }

    function openImage(data)
    {
        var image = '<img src="' + data.content + '" class="img-responsive" id="image-display" />';
        $("#content-ftp .image").html(image);
        displayContent('image');
    }

    function openVideo(data)
    {
        var video = '<video controls id="video-display"> <source src="' + data.content + '"> Your browser does not support HTML5 video. </video>';
        $("#content-ftp .video").html(video);
        displayContent('video');
    }

    function saveFile(path, content)
    {

        $.ajax({
            url: 'admin-ajax.php',
            data: {action: 'FTPWordpress', path: path, type: 'save_file', content: content},
            type: 'POST',
            dataType: 'json',
            beforeSend: function ()
            {
                blockScreen();
            },
            success: function (data)
            {
                if (data.status && data.info)
                {
                    kendo.alert("File has been saved!");
                }
                else
                {
                    kendo.alert(data.msg);
                }
            },
            complete: function ()
            {
                unblockScreen();
            },
            error: function (xhr, status, error)
            {
                kendo.alert("Server Error");
            }
        });
    }

    function actionsFile(path, type, content)
    {

        $.ajax({
            url: 'admin-ajax.php',
            data: {action: 'FTPWordpress', path: path, type: type, content: content},
            type: 'POST',
            dataType: 'json',
            beforeSend: function ()
            {
                blockScreen();
            },
            success: function (data)
            {
                if (data.status && data.info)
                {
                    kendo.confirm(data.msg).then(function ()
                    {
                        blockScreen();
                        window.location.reload();
                    }, $.noop);
                }
                else
                {
                    kendo.alert(data.msg);
                }
            },
            complete: function ()
            {
                unblockScreen();
            },
            error: function (xhr, status, error)
            {
                kendo.alert("Server Error");
            }
        });
    }

    function propertiesFile(data)
    {
        var msg = '<table class="table"">';
        msg += "<thead><th>Info</th><th>Result</th></thead>";
        msg += "<tbody>";
        for (var i in data.info)
        {
            msg += "<tr><td>" + i + "</td><td>" + data.info[i] + "</td></tr>";
        }
        msg += "</tbody>";
        msg += "</table>";
        kendo.alert(msg);
    }

    function deleteFile(data, event)
    {
        if (data.info)
        {
            kendo.alert("File Deleted with success!");
            $(event.target).remove();
        }
        else
        {
            kendo.alert(data.msg);
        }
    }

    function codeTextarea(data)
    {
        $("#content-ftp .code").html('<textarea id="code-area" style="width: 100%; height: ' + $("#sidebar-ftp").height() + 'px">' + data.content + '</textarea>');
        displayContent('code');
    }

    function codeEditor(data, path)
    {
        $("#content-ftp .code").html('<textarea id="code-area"></textarea>');
        $("#content-ftp #code-area").val(data.content);
        setTimeout(function ()
        {
            var editor = CodeMirror.fromTextArea(document.getElementById("code-area"), {
                lineNumbers: true,
                //theme: "monokai",
                extraKeys: {
                    "Ctrl-Space": "autocomplete",
                    "Ctrl-T": "toMatchingTag",
                    "Ctrl-S": function (cm)
                    {
                        var content = cm.getValue();
                        saveFile(path, content);
                    },
                    "F5": function (cm)
                    {
                        ajaxRequestAction(path, "code", null);
                        kendo.alert("Reload File");
                    }
                    ,
                    "Alt-Left": "goSubwordLeft",
                    "Alt-Right": "goSubwordRight",
                    "Ctrl-Up": "scrollLineUp",
                    "Ctrl-Down": "scrollLineDown",
                    "Shift-Ctrl-L": "splitSelectionByLine",
                    "Shift-Tab": "indentLess",
                    "Esc": "singleSelectionTop",
                    "Ctrl-L": "selectLine",
                    "Shift-Ctrl-K": "deleteLine",
                    "Ctrl-Enter": "insertLineAfter",
                    "Shift-Ctrl-Enter": "insertLineBefore",
                    "Ctrl-D": "selectNextOccurrence",
                    "Alt-CtrlUp": "addCursorToPrevLine",
                    "Alt-CtrlDown": "addCursorToNextLine",
                    "Shift-Ctrl-Space": "selectScope",
                    "Shift-Ctrl-M": "selectBetweenBrackets",
                    "Ctrl-M": "goToBracket",
                    "Shift-Ctrl-Up": "swapLineUp",
                    "Shift-Ctrl-Down": "swapLineDown",
                    "Ctrl-/": "toggleCommentIndented",
                    "Ctrl-J": "joinLines",
                    "Shift-Ctrl-D": "duplicateLine",
                    "Ctrl-T": "transposeChars",
                    "F9": "sortLines",
                    "Ctrl-F9": "sortLinesInsensitive",
                    "F2": "nextBookmark",
                    "Shift-F2": "prevBookmark",
                    "Ctrl-F2": "toggleBookmark",
                    "Shift-Ctrl-F2": "clearBookmarks",
                    "Alt-F2": "selectBookmarks",
                    "Alt-Q": "wrapLines",
                    "Ctrl-K Ctrl-Backspace": "delLineLeft",
                    "Backspace": "smartBackspace",
                    "Ctrl-K Ctrl-K": "delLineRight",
                    "Ctrl-K Ctrl-U": "upcaseAtCursor",
                    "Ctrl-K Ctrl-L": "downcaseAtCursor",
                    "Ctrl-K Ctrl-Space": "setSublimeMark",
                    "Ctrl-K Ctrl-A": "selectToSublimeMark",
                    "Ctrl-K Ctrl-W": "deleteToSublimeMark",
                    "Ctrl-K Ctrl-X": "swapWithSublimeMark",
                    "Ctrl-K Ctrl-Y": "sublimeYank",
                    "Ctrl-K Ctrl-G": "clearBookmarks",
                    "Ctrl-K Ctrl-C": "showInCenter",
                    "Ctrl-Alt-Up": "selectLinesUpward",
                    "Ctrl-Alt-Down": "selectLinesDownward",
                    "Ctrl-F3": "findUnder",
                    "Shift-Ctrl-F3": "findUnderPrevious",
                    "Shift-Ctrl-[": "fold",
                    "Shift-Ctrl-]": "unfold",
                    "Ctrl-K Ctrl-J": "unfoldAll",
                    "Ctrl-K Ctrl-0": "unfoldAll",
                    "Ctrl-H": "replace",
                },
                keyMap: "sublime",
                autoCloseTags: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                matchTags: {bothTags: true},
                showCursorWhenSelecting: true,
                indentWithTabs: true,
                foldGutter: true,
                gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter", "CodeMirror-lint-markers"],
                lint: true,
                mode: {name: data.mime, globalVars: true}
            });
            editor.setSize("100%", $("#sidebar-ftp").height());
        }, 0);
        displayContent('code');
    }

    function blockScreen(msg)
    {
        if (msg === undefined)
        {
            msg = "Loading...";
        }
        /*** Mensagem Modal loading blockUI ***/
        $.blockUI.defaults.message = msg;
        $.blockUI(
                {
                    css: {
                        fadeIn: 0,
                        border: 'none',
                        padding: '15px',
                        backgroundColor: '#444',
                        color: '#fff',
                        zIndex: 9999995
                    },
                    overlayCSS:
                            {
                                backgroundColor: '#fff',
                                zIndex: 9999993
                            }
                });
        /*** Mensagem Modal loading blockUI ***/
    }

    function unblockScreen()
    {
        $.unblockUI();
        $(".blockUI").remove();
    }

    function displayContent(name)
    {
        $("#content-ftp > *").addClass('hide');
        $("#content-ftp > ." + name).removeClass('hide');
        actionDisplayContent(name);
    }

    function actionDisplayContent(name)
    {
        if (name === "nothing")
        {
            $(".nothing").height($("#sidebar-ftp").height() - 50);
        }
    }

    $(window).on('load', function ()
    {
        setTimeout(function ()
        {
            unblockScreen();
            displayContent('nothing');
        }, 250);
    });
    $(window).resize(function ()
    {
        $(".nothing").height($("#sidebar-ftp").height() - 50);
    });
});