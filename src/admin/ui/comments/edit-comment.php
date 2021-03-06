<?php if (!defined('SCRIPTLOG')) exit(); ?>

<div class="content-wrapper">
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=(isset($pageTitle)) ? $pageTitle : ""; ?>
        <small>Control Panel</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?load=dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="index.php?load=comments">Comments</a></li>
        <li class="active"><?=(isset($pageTitle)) ? $pageTitle : ""; ?></li>
      </ol>
    </section>

 <!-- Main content -->
<section class="content">
<div class="row">
<div class="col-md-6">
<div class="box box-primary">
<div class="box-header with-border"></div>
<!-- /.box-header -->
<?php
if (isset($errors)) :
?>
<div class="alert alert-danger alert-dismissible">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<h4><i class="icon fa fa-warning"></i> Invalid Form Data!</h4>
<?php 
foreach ($errors as $e) :
echo '<p>' . $e . '</p>';
endforeach;
?>
</div>
<?php 
endif;
?>

<?php
if (isset($saveError)) :
?>
<div class="alert alert-danger alert-dismissible">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
<h4><i class="icon fa fa-ban"></i> Alert!</h4>
<?php 
echo "Error saving data. Please try again." . $saveError;
?>
</div>
<?php 
endif;
?>

<form method="post" action="index.php?load=comments&action=<?=(isset($formAction)) ? $formAction : null; ?>&commentId=<?=(isset($commentData['ID'])) ? $commentData['ID'] : 0; ?>" role="form">
<input type="hidden" name="comment_id" value="<?=(isset($commentData['ID'])) ? $commentData['ID'] : 0; ?>" >
<input type="hidden" name="post_id" value="<?=(isset($commentData['comment_post_id'])) ? $commentData['comment_post_id'] : 0; ?>" >

<div class="box-body">
<div class="form-group">
<label>Author</label>
<input type="text" class="form-control" name="author_name" placeholder="" value="
<?=(isset($commentData['comment_author_name'])) ? htmlspecialchars($commentData['comment_author_name']) : ""; ?>" required>
</div>

<div class="form-group">
<label>Content (required)</label>
<textarea class="textarea" placeholder="Place some text here"
style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;" 
name="comment_content"  maxlength="500" >
<?=(isset($commentData['comment_content'])) ? $commentData['comment_content'] : ""; ?>
</textarea>
</div>

<div class="form-group">
<label>Comment status</label>
<?=(isset($commentStatus)) ? $commentStatus : ""; ?>
</div>
<!-- /.comment status -->

</div>
<!-- /.box-body -->

<div class="box-footer">
<input type="hidden" name="csrfToken" value="<?=(isset($csrfToken)) ? $csrfToken : ""; ?>">  
<input type="submit" name="postFormSubmit" class="btn btn-primary" value="<?=(isset($commentData['ID']) && $commentData['ID'] != '') ? "Update" : ""; ?>">
</div>
</form>
            
</div>
<!-- /.box -->
</div>
<!-- /.col-md-6 -->

<div class="col-md-6">
<!-- Form Element sizes -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Response To: <?=(isset($commentData['post_title'])) ? $commentData['post_title'] : ""; ?></h3>
            </div>
            <div class="box-body">
              <div class="form-group">
                <label><i class="fa fa-calendar"></i> Submited On</label>
                <p class="text-aqua"><?=(isset($commentData['comment_date'])) ? human_readable_datetime(read_datetime($commentData['comment_date']), 'g:ia \o\n l jS F Y') : ""; ?></p>
              </div>
              <div class="form-group">
                <a href="index.php?load=reply" class="btn btn-primary"></a>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
</div>
<!-- /.col-md-6 -->
</div>
<!-- /.row --> 
</section>

</div>
<!-- /.content-wrapper -->
<script type="text/javascript">
  var loadFile = function(event) {
	  var output = document.getElementById('output');
	      output.src = URL.createObjectURL(event.target.files[0]);
	  };
</script>