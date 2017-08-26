<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FTPWordpress;

use Cake\Filesystem\Folder;
use Cake\Filesystem\File;

/**
 * Description of FTPWordpress
 *
 * @author Filipe Morelli
 */
class FTPWordpress
{

    public function start()
    {
        add_action('admin_menu', array($this, 'buildPluginInWordpress'));
        add_filter('admin_body_class', array($this, 'body_class_admin'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'));
        add_action('wp_ajax_FTPWordpress', array($this, 'ajaxAcion'));
        add_action('wp_ajax_nopriv_FTPWordpress_download_file', array($this, 'fileDownload'));
    }

    /**
     * Generate tree list in HTML
     * @param array $folders
     * @return array
     */
    private function generateTreeUlLi($folders)
    {
        $mensagem = "";
        foreach ($folders as $key => $value)
        {
            if (is_array($value))
            {
                $mensagem .= "<li class=\"dir\"><span class=\"k-sprite fa fa-folder folder\"></span>{$key}";
                $mensagem .= '<ul>';
                $mensagem .= $this->generateTreeUlLi($value);
                $mensagem .= '</ul>';
                $mensagem .= "</li>";
            }
            else if ($value != '.' || $value != '..')
            {
                $result   = $this->iconAndTypeCss($value);
                $mensagem .= "<li class=\"file {$result['type']}\" data-type=\"{$result['type']}\"><span class=\"k-sprite {$result['classCss']}\"></span>{$value}</li>";
            }
        }
        return $mensagem;
    }

    /**
     * Generates icon font structure and type
     * @param string $path
     * @return array
     */
    private function iconAndTypeCss($path)
    {
        $fileInfo = pathinfo(ABSPATH . $path);
        if (is_null($fileInfo['extension']))
        {
            return;
        }
        switch ($fileInfo['extension'])
        {
            case 'html':
                return ['classCss' => 'fa fa-html5', 'type' => 'code'];
            case 'css':
                return ['classCss' => 'fa fa-css3 code', 'type' => 'code'];
            case 'pdf':
                return ['classCss' => 'fa fa-file-pdf-o external', 'type' => 'download'];
            case 'zip':
            case 'tar':
            case 'tar.gz':
            case '7z':
            case 'gz':
            case 'rar':
                return ['classCss' => 'fa fa-archive-o external', 'type' => 'download'];
            case 'csv':
            case 'xls':
            case 'xlsx':
            case 'ods':
                return ['classCss' => 'fa fa-file-excel-o external', 'type' => 'download'];
            case 'doc':
            case 'docx':
            case 'rtf':
            case 'odt':
                return ['classCss' => 'fa fa-file-word-o external', 'type' => 'download'];
            case 'ppt':
            case 'pptx':
            case 'odp':
                return ['classCss' => 'fa fa-file-powerpoint-o external', 'type' => 'download'];
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'bmp':
                return ['classCss' => 'fa fa-file-image-o external', 'type' => 'image'];
            case 'webm':
            case 'mp4':
            case 'avi':
            case 'mkv':
            case '3gp':
            case 'rmvb':
            case 'ogg':
            case 'wmv':
            case 'flv':
            case 'amv':
            case 'm4p':
            case 'm4v':
                return ['classCss' => 'fa fa-file-video-o external', 'type' => 'video'];
            default:
                return ['classCss' => 'fa fa-file-code-o external', 'type' => 'download'];
        }
        //exit();
    }

    /**
     * Create tree path of folder
     * @param string $dir
     * @return array
     */
    private function dirToArray($dir)
    {

        $result = array();

        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            $newDir = $dir . DIRECTORY_SEPARATOR . $value;
            if (!in_array($value, array(".", "..")))
            {
                if (is_dir($newDir))
                {
                    $result[$value] = $this->dirToArray($newDir);
                }
                else
                {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Generate HTML list content
     * @param type $url
     * @return string HTML
     */
    private function generatePage($url = null)
    {
        $folders = $this->dirToArray(ABSPATH);
        $list    = $this->generateTreeUlLi($folders);

        $html = file_get_contents(__DIR__ . '/base-content.html');
        $html = str_replace("{{content}}", $list, $html);

        return $html;
    }

    /**
     * Generate Plugin in Wordpress have all methods wordpress
     */
    function buildPluginInWordpress()
    {
        add_menu_page(
                'FTP Wordpress', // page title
                'FTP Wordpress', // menu title
                'manage_options', // capability
                'ftp-wordpress', // menu slug
                array($this, 'generateHtmlContentPage') // callback function
        );
    }

    /**
     * create html page
     */
    public function generateHtmlContentPage()
    {
        print '<div class="wrap">';
        print $this->generatePage();
        print '</div>';
    }

    /**
     * Set important css classes in body admin
     * @param string $classes
     * @return type
     */
    public function body_class_admin($classes)
    {
        if (get_admin_page_parent() == "ftp-wordpress")
        {
            return $classes . ' folded ftp-wordpress';
        }
    }

    /**
     * Load All js and css scripts
     */
    public function load_admin_scripts()
    {
        //bower
        //wp_enqueue_style('admin_css', get_template_directory_uri() . '/admin-style.css', false, '1.0.0');

        wp_enqueue_style('FE_fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('FE_bootstrap', '/' . PLUGINDIR . '/FileExplorer/assets/css/bootstrap.min.css', '3.7.3');
        wp_enqueue_style('FE_kendo_common_material', '/' . PLUGINDIR . '/FileExplorer/assets/css/kendo.common-material.min.css');
        wp_enqueue_style('FE_kendo_material', '/' . PLUGINDIR . '/FileExplorer/assets/css/kendo.material.min.css');
        wp_enqueue_style('FE_kendo_material_mobile', '/' . PLUGINDIR . '/FileExplorer/assets/css/kendo.material.mobile.min.css');
        wp_enqueue_script('FE_jquery', '/' . PLUGINDIR . '/FileExplorer/assets/js/jquery.js', '1.12.3');
        wp_enqueue_script('FE_jquery.block.ui', '/' . PLUGINDIR . '/FileExplorer/assets/js/jquery.block.ui.js', '2.70.0');
        wp_enqueue_script('FE_kendo_ui', '/' . PLUGINDIR . '/FileExplorer/assets/js/kendo.all.min.js');
        $this->code_mirror_modules();
        wp_enqueue_style('FE_main_css', '/' . PLUGINDIR . '/FileExplorer/assets/css/main.css');
        wp_enqueue_script('FE_main_js', '/' . PLUGINDIR . '/FileExplorer/assets/js/main.js');
    }

    /**
     * Load CSS and JS modules of codemirror
     */
    public function code_mirror_modules()
    {
        wp_enqueue_style('FE_codemirror', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/lib/codemirror.css');
        wp_enqueue_style('FE_codemirror_show-hint', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/hint/show-hint.css');
        wp_enqueue_style('FE_codemirror_foldgutter', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/foldgutter.css');
        wp_enqueue_style('FE_codemirror_dialog', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/dialog/dialog.css');

        wp_enqueue_script('FE_codemirror', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/lib/codemirror.js');
        wp_enqueue_script('FE_codemirror_addon-hint_javascript', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/hint/javascript-hint.js');
        wp_enqueue_script('FE_codemirror_addon-hint_xml', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/hint//xml-hint.js');
        wp_enqueue_script('FE_codemirror_addon-hint_html', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/hint/html-hint.js');
        wp_enqueue_script('FE_codemirror_addon-anyword-hint', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/hint/anyword-hint.js');
        wp_enqueue_script('FE_codemirror_addon-searchcursor', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/search/searchcursor.js');
        wp_enqueue_script('FE_codemirror_addon-search', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/search/search.js');
        wp_enqueue_script('FE_codemirror_addon-dialog', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/dialog/dialog.js');

        wp_enqueue_script('FE_codemirror_addon-matchbrackets', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/edit/matchbrackets.js');
        wp_enqueue_script('FE_codemirror_addon-closebrackets', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/edit/closebrackets.js');
        wp_enqueue_script('FE_codemirror_addon-matchtags', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/edit/matchtags.js');
        wp_enqueue_script('FE_codemirror_addon-closetag', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/edit/closetag.js');

        wp_enqueue_script('FE_codemirror_addon-comment', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/comment/comment.js');

        wp_enqueue_script('FE_codemirror_addon-hardwrap', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/wrap/hardwrap.js');
        wp_enqueue_script('FE_codemirror_addon-foldcode', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/foldcode.js');
        wp_enqueue_script('FE_codemirror_addon-xml-fold', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/xml-fold.js');
        wp_enqueue_script('FE_codemirror_addon-brace-fold', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/brace-fold.js');
        wp_enqueue_script('FE_codemirror_addon-foldgutter', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/foldgutter.js');
        wp_enqueue_script('FE_codemirror_addon-indent-fold', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/indent-fold.js');
        wp_enqueue_script('FE_codemirror_addon-markdown-fold', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/markdown-fold.js');
        wp_enqueue_script('FE_codemirror_addon-comment-fold', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/fold/comment-fold.js');

        wp_enqueue_script('FE_codemirror_mode_javascript', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/javascript/javascript.js');
        wp_enqueue_script('FE_codemirror_mode_jxml', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/xml/xml.js');
        wp_enqueue_script('FE_codemirror_mode_css', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/css/css.js');
        wp_enqueue_script('FE_codemirror_mode_sass', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/sass/sass.js');
        wp_enqueue_script('FE_codemirror_mode_htmlmixed', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/htmlmixed/htmlmixed.js');
        wp_enqueue_script('FE_codemirror_mode_coffeescript', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/coffeescript/coffeescript.js');
        wp_enqueue_script('FE_codemirror_mode_perl', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/perl/perl.js');
        wp_enqueue_script('FE_codemirror_mode_clike', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/clike/clike.js');
        wp_enqueue_script('FE_codemirror_mode_php', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/php/php.js');
        wp_enqueue_script('FE_codemirror_mode_sass', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/sass/sass.js');
        wp_enqueue_script('FE_codemirror_mode_sql', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/sql/sql.js');

        wp_enqueue_script('FE_codemirror_show-hint', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/addon/hint/show-hint.js');
        wp_enqueue_script('FE_codemirror_mode_markdown', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/mode/markdown/markdown.js');

        wp_enqueue_script('FE_codemirror_keymap_sublime', '/' . PLUGINDIR . '/FileExplorer/assets/codemirror/keymap/sublime.js');
    }

    /**
     * Events of File copy, rename others
     * @param array $action [path, event(type)]
     */
    public function eventFile($actions)
    {
        $return = [
            'status' => true
        ];
        $file   = new File($actions['completeUrl']);
        switch ($actions['type'])
        {
            case 'properties':
                $fileInfo                = $file->info();
                $fileInfo['filesize']    = $this->FileSizeText($file->path);
                $fileInfo['permision']   = $file->perms();
                $fileInfo['group']       = $file->group();
                $fileInfo['owner']       = $file->owner();
                $fileInfo['last access'] = date('Y-m-d H:i:s', $file->lastAccess());
                $fileInfo['last change'] = date('Y-m-d H:i:s', $file->lastChange());
                $fileInfo['md5']         = $file->md5();

                $return['info']      = $fileInfo;
                break;
            case 'editor':
            case 'code':
                $fileInfo['content'] = $file->read();
                $fileInfo['mime']    = $this->FileMimeType($file);
                $return['info']      = $fileInfo;
                break;
            case 'textarea':
                $fileInfo['content'] = $file->read();
                $return['info']      = $fileInfo;
                break;
            case 'delete':
                $return['info']      = $file->delete();
                if (!$return['info'])
                {
                    $return['msg'] = "Error: file could not be deleted!";
                }
                break;
            case 'save_file':
                $file->open('w');
                $resutlt        = $file->write(stripslashes($actions['content']));
                $file->close();
                $return['info'] = $resutlt;
                if (!$resutlt)
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            case 'copy':
                $return['info'] = $file->copy(ABSPATH . $actions['content']);
                if (!$resutlt)
                {
                    $return['msg'] = "File has been copied!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            case 'move':
                $content = ABSPATH . $actions['content'];
                if (is_dir($content))
                {
                    $path           = $content . DIRECTORY_SEPARATOR . basename($actions['path']);
                    $return['info'] = $file->copy($path);
                }
                else
                {
                    $return['info'] = $file->copy($content);
                }
                if ($return['info'])
                {
                    $file->delete();
                    $return['msg'] = "File has been moved!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!, {$content}, {$path} ";
                }
                break;
            case 'rename':
                $return['info'] = rename(ABSPATH . $actions['path'], dirname(ABSPATH . $actions['path']) . DIRECTORY_SEPARATOR . $actions['content']);
                if (!$resutlt)
                {
                    $return['msg'] = "File has been rename!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            case 'upload':
                $return            = $this->doUpLoadFiles($file, $actions);
                break;
            case 'image':
            case 'video':
                $return['info']    = true;
                $return['content'] = site_url() . DIRECTORY_SEPARATOR . $actions['path'];
                break;
            case 'folder':
                $return['info']    = $file->folder()->create(dirname(ABSPATH . $actions['path']) . DIRECTORY_SEPARATOR . $actions['content']);
                if ($return['info'])
                {
                    $return['msg'] = "{$actions['content']} has been created!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            default:
                break;
        }
        return $return;
    }

    /**
     * Events of Folder copy, rename others
     * @param array $action [path, event(type)]
     */
    public function eventFolder($actions)
    {
        $return = [
            'status' => true
        ];
        $folder = new Folder($actions['completeUrl']);
        switch ($actions['type'])
        {
            case 'properties':
                $folderInfo              = pathinfo($folder->path);
                $folderInfo['filesize']  = $this->FileSizeText($folder->path);
                $folderInfo['permision'] = substr(sprintf('%o', fileperms($folder->path)), -4);

                $return['info'] = $folderInfo;
                break;
            case 'delete':
                $return['info'] = $folder->delete();
                if (!$return['info'])
                {
                    $return['msg'] = "Error: file could not be deleted!";
                }
                break;
            case 'copy':
                $return['info'] = $folder->copy(ABSPATH . $actions['content']);
                if (!$resutlt)
                {
                    $return['msg'] = "File has been copied!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            case 'move':
                $content = ABSPATH . $actions['content'];
                if (is_dir($content))
                {
                    $path           = $content . DIRECTORY_SEPARATOR . basename($actions['path']);
                    $return['info'] = $folder->copy($path);
                }
                else
                {
                    $return['info'] = $folder->copy($content);
                }
                if ($return['info'])
                {
                    $folder->delete();
                    $return['msg'] = "File has been moved!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!, {$content}, {$path} ";
                }
                break;
            case 'rename':
                $return['info'] = rename(ABSPATH . $actions['path'], dirname(ABSPATH . $actions['path']) . DIRECTORY_SEPARATOR . $actions['content']);
                if (!$resutlt)
                {
                    $return['msg'] = "File has been rename!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            case 'upload':
                $return            = $this->doUpLoadFiles($folder, $actions);
                break;
            case 'image':
            case 'video':
                $return['info']    = true;
                $return['content'] = site_url() . DIRECTORY_SEPARATOR . $actions['path'];
                break;
            case 'folder':
                $return['info']    = $folder->create(ABSPATH . $actions['path'] . DIRECTORY_SEPARATOR . $actions['content']);
                if ($return['info'])
                {
                    $return['msg'] = "{$actions['content']} has been created!<br>Do you want to reload to see new tree?";
                }
                else
                {
                    $return['msg'] = "Error: changes not save!";
                }
                break;
            default:
                break;
        }
        return $return;
    }

    /**
     * Do upload Files
     * @param type $fileOrFolder 
     * @param type $actions
     */
    public function doUploadFiles($fileOrFolder, $actions)
    {
        $path = $fileOrFolder->path;
        if (!is_dir($fileOrFolder->path))
        {
            $path = dirname($fileOrFolder->path); // getPath that want to upload
        }
        foreach ($actions['files'] as $key => $value)
        {
            $newFile = new File($value['tmp_name'], '777', true);
            $result  = $newFile->copy($path . DIRECTORY_SEPARATOR . $value['name']);
            if (!$result)
            {
                return [
                    'status' => false,
                    'info'   => false,
                    'msg'    => 'File ' . $value['name'] . ' was not uploaded!<br> Action broked! ' . $value['tmp_name'] . '<br>' . $path . DIRECTORY_SEPARATOR . $value['name']
                ];
            }
            $newFile->delete();
        }
        return [
            'status' => true,
            'info'   => true,
            'msg'    => 'Files has been uploaded!'
        ];
    }

    /**
     * get file mime type for codemirro
     * @param \Cake\Filesystem\File $file
     * @return string
     */
    private function FileMimeType(File $file)
    {
        $extension = $file->info()['extension'];
        switch ($extension)
        {
            case 'css':
                return 'text/css';
            case 'less':
                return 'text/x-less';
            case 'scss':
                return 'text/x-scss';
            case 'coffee':
                return 'text/coffeescript';
            case 'xml':
                return 'application/xml';
            case 'html':
                return 'text/html';
            case 'js':
                return 'text/javascript';
            case 'json':
                return 'text/json';
            case 'ts':
                return 'text/typescript';
            case 'pl':
            case 'plx':
                return 'text/x-perl';
            case 'php':
                return 'application/x-httpd-php';
            case 'sass':
                return 'text/x-sass';
            case 'sql':
                return 'text/x-sql';
            case 'txt':
                return 'text/plain';
            default:
                return $file->info()['mime'];
        }
    }

    /**
     * Generate File Size Text in string 200b, 7KB
     * @param type $path
     * @return type
     */
    private function FileSizeText($path)
    {
        $bytes = sprintf('%u', filesize($path));

        if ($bytes > 0)
        {
            $unit  = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');

            if (array_key_exists($unit, $units) === true)
            {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }

        return $bytes;
    }

    /**
     * Ajax Method answer
     */
    public function ajaxAcion()
    {
        $return = ['status' => false];
        if ($this->isAuth())
        {
            $actions                = $_POST;
            $actions['files']       = $_FILES;
            $actions['completeUrl'] = ABSPATH . $actions['path'];
            if (is_file($actions['completeUrl']))
            {
                $return = $this->eventFile($actions);
            }
            else if (is_dir($actions['completeUrl']))
            {
                $return = $this->eventFolder($actions);
            }
        }
        else
        {
            $return = ['msg' => 'Not Auth'];
        }
        echo json_encode($return);
        wp_die();
    }

    /**
     * File Download Method
     * @global type $wp_query
     */
    public function fileDownload()
    {
        if ($this->isAuth())
        {
            $file = ABSPATH . $_GET['path'];
            if (file_exists($file))
            {
                header("Content-Description: File Transfer");
                header("Content-Type: application/octet-stream");
                header('Content-Length: ' . filesize($file));
                header("Content-Disposition: attachment; filename='" . basename($file) . "'");
                readfile($file);
                exit();
            }
        }
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part(404);
        exit();
    }

    public function isAuth()
    {
        return is_user_logged_in() && is_admin();
    }

}
