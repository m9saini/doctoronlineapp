
							
							<div class="form-group col-md-6 col-xs-12 col-sm-6">
								<label for="exampleInputEmail1">Page Title</label><span style="color:red;">*</span></label>
								<?php
									$data = array(
												'name'			=> 'title',
												'value'     	=> (isset($page_data['title']))?$page_data['title']:'',
												'id'			=> 'page_title',
												'required'		=>"required",
												'placeholder'	=> 'Page Title',
												'title'			=> 'Page Title',
												'class'			=> "form-control",
												 'autofocus'

												);
									echo form_input($data);
								?>
							</div>
                            <?php /*
                            <div class="form-group col-md-6 col-xs-12 col-sm-6">
								<label for="exampleInputEmail1">Menu</label>
									<select class="form-control"  name="menu" id="menu">
                                    	<option  value="0" selected="selected" >Main Menu</option>
                                        <?php
											foreach($mainmenus as $key=>$value){
										?>
                                        	<option  value="<?php echo $value['id'] ?>" ><?php echo $value['title'] ?></option>
                                        <?php } ?>
                                    </select>
							</div> */ ?>
                            
							<div class="form-group col-md-6 col-xs-12 col-sm-6">
								<label for="exampleInputEmail1">Page Heading</label><span style="color:red;">*</span></label>
								<?php
									$data = array(
												'name'			=> 'heading',
												'value'     	=> (isset($page_data['heading']))?$page_data['heading']:'',
												'id'			=> 'page_heading',
												'required'		=>"required",
												'placeholder'	=> 'Page Heading',
												'title'			=> 'Page Heading',
												'class'			=> "form-control",
												 'autofocus'

												);
									echo form_input($data);
								?>
							</div>
							

							<div class="form-group col-md-12 col-xs-12 col-sm-12">
								<label for="exampleInputEmail1">Page Content<span style="color:red;">*</span></label>
								<?php
									$data = array(
												'name'			=> 'content',
												'value'     	=> (isset($page_data['content']))?$page_data['content']:'',
												'id'			=> 'content',
												'required'		=> "required",
												'rows'			=> 10,
												'cols'			=> 80,
												'placeholder'	=> 'Page Description',
												'title'			=> 'Page',
												'class'			=> "ckeditor",
												);
									echo form_textarea($data);
								?>
							</div>


							<div class="clearfix"></div>
                           <?php /* 
                            <div class="form-group col-md-6 col-xs-12 col-sm-6">
								<label for="exampleInputEmail1">Menu Location</label>
									<select class="form-control"  name="menu_loacation[]" id="menu_loacation" multiple="multiple">
                                    	<option  value="1" >Header-Menu</option>
                                        <option  value="2">Main-Menu</option>
                                        <option  value="3" selected="selected">Footer-Menu</option>
                                    </select>
							</div>
							<div class="form-group col-md-6 col-xs-12 col-sm-6">
								<label for="exampleInputEmail1">Page Image</label>
                                
								<?php
									$data = array(
												'name'			=> 'page_image',
												'id'			=> 'page_image',
												'title'			=> 'Page Image',
												'accept'        => 'image/*',
												);
									echo form_upload($data);
								?>
							</div>	*/ ?>
