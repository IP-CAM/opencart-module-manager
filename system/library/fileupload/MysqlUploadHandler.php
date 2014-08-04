<?php 


class MysqlUploadHandler extends UploadHandler {

    protected function initialize() {
        $this->db = new mysqli(
            DB_HOSTNAME,
            DB_USERNAME,
            DB_PASSWORD,
            DB_DATABASE
        );
        parent::initialize();
        $this->db->close();
    }

    public function get($print_response = true) {
        $images = array();
        $query = "SELECT `image`, `sort_order` FROM `product_image` WHERE `product_id` = " . $this->options['product_id'] . ' ORDER BY `sort_order` ASC';

        if ($result = $this->db->query($query)) {
            while ($img = $result->fetch_row()) {
                $filename = basename($img[0]);
                $url = DIR_IMAGE . 'data/' . $filename;

                if (file_exists($url)) {
                    $images[] = array(
                        'name' => $filename,
                        'url_to_store' => $img[0],
                        'sort_order' => $img[1],
                        'url' => $url,
                        'deleteType' => 'DELETE',
                        'deleteUrl' => '/system/library/fileupload/?file=' . $url . '&product_id=' . $this->options['product_id'],
                        'size' => $this->get_file_size($url)
                    );
                }
            }
        }

        echo json_encode(array('files' => $images));
    }

}