<?php $__env->startSection('page_title','Dashboard'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Dashboard</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>150</h3><p>New Orders</p></div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>53<sup style="font-size:20px">%</sup></h3><p>Bounce Rate</p></div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>44</h3><p>User Registrations</p></div>
            <div class="icon"><i class="fas fa-user-plus"></i></div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>65</h3><p>Unique Visitors</p></div>
            <div class="icon"><i class="fas fa-chart-pie"></i></div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <section class="col-lg-7 connectedSortable">
        <div class="card">
            <div class="card-header border-0"><h3 class="card-title"><i class="fas fa-chart-area me-2"></i>Sales</h3></div>
            <div class="card-body">
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-0"><h3 class="card-title"><i class="far fa-comments me-2"></i>Direct Chat</h3></div>
            <div class="card-body" style="height:260px;">
                <p class="text-muted">Área para chat ou avisos rápidos.</p>
            </div>
        </div>
    </section>

    <section class="col-lg-5 connectedSortable">
        <div class="card bg-gradient-info">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-map-marker-alt me-2"></i>Visitors</h3>
            </div>
            <div class="card-body">
                <div id="world-map" style="height:250px; width:100%;"></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-0"><h3 class="card-title"><i class="fas fa-chart-pie me-2"></i>Sales Graph</h3></div>
            <div class="card-body">
                <input type="text" class="knob" value="80" data-width="90" data-height="90" data-fgColor="#3c8dbc" data-readonly="true">
            </div>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(function(){
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul'],
            datasets: [{
                label: 'Sales',
                data: [30,45,40,65,70,60,80],
                backgroundColor: 'rgba(60,141,188,0.2)',
                borderColor: 'rgba(60,141,188,1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}}
    });

    $('#world-map').vectorMap({
        map: 'world_en',
        backgroundColor: 'transparent',
        color: '#f4f4f4',
        hoverOpacity: 0.7,
        selectedColor: '#666666',
        enableZoom: true,
        showTooltip: true,
        values: {BR: 25, US: 50, CA: 15, DE: 20, FR: 18},
        scaleColors: ['#C8EEFF', '#006491'],
        normalizeFunction: 'polynomial'
    });

    $('.knob').knob();
});
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>