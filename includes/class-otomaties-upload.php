<?php
class Otomaties_Upload
{

    private $ID;

    public function __construct(int $id)
    {
        $this->ID = $id;
    }

    public function get_ID()
    {
        return $this->ID;
    }

    public function is_private()
    {
        $upload_dir = new Otomaties_Upload_Directory;
        return strpos(get_attached_file($this->get_ID()), (string) $upload_dir->private_dir()) === 0;
    }

    public function make_private()
    {
        $upload_dir = wp_get_upload_dir();
        $upload_directory = new Otomaties_Upload_Directory;

        // Create month_year_directory
        $month_year_upload_directory = $upload_directory->private_dir();
        $month_year_upload_directory->create();
        
        $this->move($month_year_upload_directory->append(date('Y') . '/' . date('m')));
    }

    public function make_public()
    {
        $upload_dir     = wp_get_upload_dir();
        $path           = new Otomaties_Path($upload_dir['basedir']);
        $this->move($path->append(date('Y') . '/' . date('m')));
    }

    public function move($new_path)
    {
        $upload_dir     = wp_get_upload_dir();
        $meta           = wp_get_attachment_metadata($this->get_ID());
        $filename           = basename(get_attached_file($this->get_ID()));
        $original_file_path = get_attached_file($this->get_ID());
        $new_file_path      = $new_path . $filename;
        $backup_sizes = get_post_meta($this->get_ID(), '_wp_attachment_backup_sizes', true);
        $full_size_file_name = isset($backup_sizes['full-orig']) && isset($backup_sizes['full-orig']['file']) ? $backup_sizes['full-orig']['file'] : false;

        if (isset($meta['file'])) {
            $meta['file'] = str_replace($upload_dir['basedir'] . '/', '', $new_file_path);
        }

        if (!is_dir(dirname($new_file_path))) {
            mkdir(dirname($new_file_path), 0777, true);
        }

        if (!is_dir($new_path)) {
            mkdir($new_path, 0777, true);
        }

        // Rename the original file
        if (file_exists($original_file_path)) {
            rename($original_file_path, $new_file_path);
        }

        if (isset($meta['original_image'])) {
            // Rename the original image
            rename(dirname($original_file_path) . '/' . $meta['original_image'], $new_path . '/' . $meta['original_image']);
        }

        if (isset($meta['sizes']) && !empty($meta['sizes'])) {
            foreach ((array)$meta['sizes'] as $size => $meta_size) {
                $size_old_filepath = dirname($original_file_path) . '/' . $meta['sizes'][$size]['file'];
                $size_new_filepath = $new_path . '/' . $meta['sizes'][$size]['file'];
                if (file_exists($size_old_filepath)) {
                    rename($size_old_filepath, $size_new_filepath);
                }
            }
        }

        $upload_directory = new Otomaties_Upload_Directory;
        $this->find_replace(str_replace($upload_dir['basedir'] . '/', '', $original_file_path), str_replace($upload_dir['basedir'] . '/', '', $new_file_path));

        if ($full_size_file_name) {
            $full_size_original_file_path = str_replace($filename, $full_size_file_name, $original_file_path);
            $full_size_new_file_path = str_replace($filename, $full_size_file_name, $new_file_path);
            rename($full_size_original_file_path, $full_size_new_file_path);
            $this->find_replace(str_replace($upload_dir['basedir'] . '/', '', $full_size_original_file_path), str_replace($upload_dir['basedir'] . '/', '', $full_size_new_file_path));
        }
    }

    private function find_replace($original, $new)
    {
        $extension  = pathinfo($original, PATHINFO_EXTENSION);
        $find       = str_replace('.' . $extension, '', $original);
        $replace    = str_replace('.' . $extension, '', $new);

        $db = array(
            'name' => constant('DB_NAME'),
            'user' => constant('DB_USER'),
            'pass' => constant('DB_PASSWORD'),
            'host' => constant('DB_HOST'),
            'search' => $find,
            'replace' => $replace,
            'dry_run' => false
        );
        $icit_srdb = new icit_srdb($db);
    }
}
