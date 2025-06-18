<?php
get_header();

// Get and sanitize the start and end date from query string
$start_date_value = isset($_GET['start-date']) ? sanitize_text_field($_GET['start-date']) : '';
$end_date_value   = isset($_GET['end-date']) ? sanitize_text_field($_GET['end-date']) : '';

// Convert to Y-m-d format
if (!empty($start_date_value)) {
    $start_date_object = DateTime::createFromFormat('Y-m-d', $start_date_value);
    if ($start_date_object !== false) {
        $start_date_value = $start_date_object->format('Y-m-d');
    }
}

if (!empty($end_date_value)) {
    $end_date_object = DateTime::createFromFormat('Y-m-d', $end_date_value);
    if ($end_date_object !== false) {
        $end_date_value = $end_date_object->format('Y-m-d');
    }
}
?>

<div class="container">
    <div class="row pb-lg-3 pb-2">
        <form action="<?php echo get_post_type_archive_link('project'); ?>" method="get" class="row g-3">
            <div class="col-md-3 col-12">
                <label for="start-date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start-date" name="start-date" value="<?php echo esc_attr($start_date_value); ?>">
            </div>

            <div class="col-md-3 col-12">
                <label for="end-date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end-date" name="end-date" value="<?php echo esc_attr($end_date_value); ?>">
            </div>

            <div class="col-md-4 col-12 d-flex align-items-end justify-content-center">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
           <!-- Reset Button -->
        <div class="col-md-2 col-12 d-flex align-items-end  justify-content-center">
            <button type="button" id="reset-filter" class="btn btn-danger w-100 text-white">
                ✖ Reset Filters
            </button>
        </div>
        </form>

        
    </div>
</div>

<div class="container">
    <div class="row">

    <?php
    $posts_per_page = -1;
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Build meta_query using only 'strt_date'
    $meta_query = [];

    if (!empty($start_date_value) && !empty($end_date_value)) {
        $meta_query[] = [
            'key'     => 'strt_date',
            'value'   => [$start_date_value, $end_date_value],
            'compare' => 'BETWEEN',
            'type'    => 'DATE',
        ];
    } elseif (!empty($start_date_value)) {
        $meta_query[] = [
            'key'     => 'strt_date',
            'value'   => $start_date_value,
            'compare' => '>=',
            'type'    => 'DATE',
        ];
    }

    $args = [
        'post_type'      => 'project',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'meta_query'     => $meta_query,
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        echo '<div class="row">';
        while ($query->have_posts()) : $query->the_post(); ?>
            <div class="col-lg-4 col-md-6 col-sm-12 p-0">
                <div class="m-2 p-3 border">
                    <a href="<?php the_permalink(); ?>">
                        <img src="<?php echo get_the_post_thumbnail_url(); ?>" class="img-fluid" alt="<?php the_title(); ?>">
                    </a>
                    <h2 class="pt-2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

                    <?php if (get_field('project_name')) : ?>
                        <p><strong>Project Name:</strong> <?php the_field('project_name'); ?></p>
                    <?php endif; ?>

                    <?php if ($date = get_field('strt_date')) : ?>
                        <p><strong>Project Start Date:</strong> <?php echo date('m/d/Y', strtotime($date)); ?></p>
                    <?php endif; ?>

                    <?php if ($date = get_field('end_date')) : ?>
                        <p><strong>Project End Date:</strong> <?php echo date('m/d/Y', strtotime($date)); ?></p>
                    <?php endif; ?>

                    <?php if (get_field('project_url')) : ?>
                        <p><strong>Project URL:</strong>
                            <a href="<?php the_field('project_url'); ?>"><?php the_field('project_url'); ?></a>
                        </p>
                    <?php endif; ?>

                    <?php if (get_field('project_desc')) : ?>
                        <span><strong>Project Description:</strong> <?php the_field('project_desc'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php 
            if ($query->current_post % 3 == 2) {
                echo '</div><div class="row">';
            }
        endwhile;

        echo '</div>';

        echo paginate_links([
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'current'   => max(1, get_query_var('paged')),
            'total'     => $query->max_num_pages,
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
        ]);

    else :
        echo '<p>No posts found.</p>';
    endif;

    wp_reset_postdata();
    ?>

    </div>
</div>


<script>
document.getElementById('reset-filter').addEventListener('click', function () {
    this.classList.add('loading');
    document.getElementById('start-date').value = '';
    document.getElementById('end-date').value = '';

    window.location.href = "<?php echo get_post_type_archive_link('project'); ?>";
});
</script>

<style>
#reset-filter.loading::after {
    content: ' ⏳';
    margin-left: 10px;
}
</style>

<?php get_footer(); ?>
