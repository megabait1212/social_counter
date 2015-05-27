<?php

class Top_Posts_Widget extends WP_Widget {

    function Top_Posts_Widget() {
        parent::WP_Widget(false, 'Top Posts Widget');
    }

    public function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        //$desc = isset($instance['description']) ? esc_attr($instance['description']) : '';
        if (!isset($instance['description']) || !$desc = $instance['description'])
            $desc = __('Click on any of the following buttons on the post\'s to make your vote count! Like button, Tweet, G+, Tumblr or Pinterest. The "share" button doesn\'t count as voting. Thanks and enjoy!');
        if (!isset($instance['number']) || !$number = (int) $instance['number'])
            $number = 10;
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'tc'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'tc'); ?></label>
            <input id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Tooltip description:', 'tc'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('description')); ?>" name="<?php echo esc_attr($this->get_field_name('description')); ?>" type="text" value="<?php echo esc_attr($desc); ?>" />
        </p>

        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['description'] = strip_tags($new_instance['description']);
        return $instance;
    }

    public function widget($args, $instance) {
        extract($args);
        echo $before_widget;

        $query = array(
            'meta_key' => 'social_count',
            'order' => 'desc',
            'orderby' => 'meta_value_num',
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => isset($instance['number']) ? (int) $instance['number'] : 10,
        );
        $posts = new WP_Query($query);
        ;
        ?>
        <?php if ($posts->have_posts()): ?>
            <?php $title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base); ?>
            <div class="artist-top block">
                <header class="clearfix">
                    <?php
                    if ($title)
                        echo '<h3 class="pull-left">' . $title . '</h3>';
                    ?>
                    <span class="info-tooltip pull-right" data-toggle="tooltip" data-placement="top" data-container="body" data-content="<?php echo isset($instance['description']) ? esc_attr($instance['description']) : __(''); ?>"><i class="fa fa-info-circle"></i></span>
                </header>

                <ul class="clearfix">
                    <?php while ($posts->have_posts()) : $posts->the_post(); ?>     
                        <li class="artist-top-item clearfix">
                            <a href="<?php the_permalink(); ?>" class="artist-title pull-left" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                            <span class="vote-wrap pull-right"><?php echo get_post_meta(get_the_ID(), 'social_count', true); ?> <span class="votes"><i class="fa fa-thumbs-up"></i></span></span>
                            <div class="clear"></div>
                        </li>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        echo $after_widget;
    }

}
