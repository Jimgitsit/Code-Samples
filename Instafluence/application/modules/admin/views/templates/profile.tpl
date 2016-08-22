<form action="/influenceredit/saveProfile/" method="POST" role="form" id="profileEditForm">
	<section id="profile">
		<div class="container">

			<div class="row">
				<div class="col-xs-4">

					<div class="form-group">
						<label for="tier">Tier</label>
						<select id="tier" name="tier" class="select2-no-clear form-control input-sm">
							<option></option>
							{foreach $tiers as $value => $display}
								<option value="{$value}" {if isset($profile.tier) && $profile.tier == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>

						<br/>

						<div class="col-xs-4">
							<div class="form-group">
								<br/>
								<label for="cpp">Cost per Post (CPP)</label>
								<div class="input-group input-group-sm">
									<span class="input-group-addon">$</span>
									<input type="text" id="cpp" name="cpp" value="{if isset($profile.cpp) && is_numeric($profile.cpp)}{$profile.cpp|number_format:2:".":""}{/if}" class="form-control" />
								</div>
							</div>
						</div>

						<div class="col-xs-4">
							<div class="form-group">
								<label for="cpe">Cost Per Engagement (CPE)</label>
								<div class="input-group input-group-sm">
									<span class="input-group-addon">$</span>
									<input type="text" id="cpe" name="cpe" value="{if isset($profile.cpe) && is_numeric($profile.cpe)}{$profile.cpe|number_format:2:".":""}{/if}" class="form-control" />
								</div>
							</div>
						</div>

						<div class="col-xs-4">
							<div class="form-group">
								<label for="cpm">Cost Per Thousands (CPM)</label>
								<div class="input-group input-group-sm">
									<span class="input-group-addon">$</span>
									<input type="text" id="cpm" name="cpm" value="{if isset($profile.cpm) && is_numeric($profile.cpm)}{$profile.cpm|number_format:2:".":""}{/if}" class="form-control" />
								</div>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="dob">Date of Birth</label>
						<input type="text" id="dob" name="dob" value="{if isset($profile.dob)}{$profile.dob|substr:11|date_format:'%m/%d/%Y'}{/if}" class="form-control input-sm" />
					</div>

					<label class="radio-inline">
						<input type="radio" name="gender" value="1" class="input-sm" {if isset($profile.gender) && $profile.gender == 1}checked{/if}>Male
					</label>
					<label class="radio-inline">
						<input type="radio" name="gender" value="0" class="input-sm" {if isset($profile.gender) && $profile.gender == 0}checked{/if}>Female
					</label>
					<!-- <label class="radio-inline">
						<input type="radio" name="gender" value="2" class="input-sm" {if isset($profile.gender) && $profile.gender == 2}checked{/if}>Other
					</label> -->

					<div class="form-group">
						<label for="address">Address 1</label>
						<input type="text" id="address" name="address" value="{if isset($profile.address)}{$profile.address}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="address2">Address 2</label>
						<input type="text" id="address2" name="address2" value="{if isset($profile.address2)}{$profile.address2}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="city">City</label>
						<input type="text" id="city" name="city" value="{if isset($profile.city)}{$profile.city}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="state">State</label>
						<input type="text" id="state" name="state" value="{if isset($profile.state)}{$profile.state}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="zip">Zip</label>
						<input type="text" id="zip" name="zip" value="{if isset($profile.zip)}{$profile.zip}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="addr-country">Country</label>
						<select class="select2 form-control input-sm" name="country" id="addr-country">
							<option value="" disabled selected>Select Country</option>
							{foreach $countries as $value => $display}
								<option value="{$value}" {if $value == '-'}disabled{/if} {if isset($profile.country) && $profile.country == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="music_genre">Music Genre</label>
						<select id="music_genre" name="music_genre[]" class="select2 form-control input-sm" multiple>
							{foreach $musicGenres as $value => $display}
								<option value="{$value}" {if isset($profile.music_genre[$value]) && $profile.music_genre[$value]}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="promoting">Types of promotions</label>
						<select id="promoting" name="promoting[]" class="select2 form-control input-sm" multiple>
							{foreach $promotionTypes as $value => $display}
								<option value="{$value}" {if isset($profile.promoting[$value]) && $profile.promoting[$value]}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="hobbies">Interests/Hobbies</label>
						<select id="hobbies" name="hobbies[]" class="select2 form-control input-sm" multiple>
							{foreach $hobbies as $value => $display}
								<option value="{$value}" {if isset($profile.hobbies[$value]) && $profile.hobbies[$value]}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

				</div>

				<div class="col-xs-4">

					<div class="form-group">
						<label for="paypal">Paypal ID</label>
						<input type="text" id="paypal" name="paypal" value="{if isset($profile.paypal)}{$profile.paypal}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="phone">Phone</label>
						<input type="text" id="phone" name="phone" value="{if isset($profile.phone)}{$profile.phone}{/if}" class="form-control input-sm" />
					</div>

					<label class="checkbox-inline">
						<input type="hidden" name="exclusive" value="false" />
						<input type="checkbox" id="exclusive" name="exclusive" value="true" {if isset($profile.exclusive)}{if $profile.exclusive}checked{/if}{/if} /> Exclusive
					</label>

					<div class="form-group">
						<label for="categories">Content Categories</label>
						<select id="categories" name="categories[]" class="select2 form-control input-sm" multiple>
							{foreach $contentCategories as $value => $display}
								<option value="{$value}" {if isset($profile.categories[$value]) && $profile.categories[$value]}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="height">Height</label>
						<select id="height" name="height" class="select2 form-control">
							<option></option>
							{foreach $heightOptions as $value => $display}
								<option value="{$value}" {if isset($profile) && ($profile.height == $value || $profile.height == $display)}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="shoe_size">Shoe Size</label>
						<select id="shoe_size" name="shoe_size" class="select2 form-control input-sm">
							<option></option>
							{for $i=4 to 14 step=0.5}
								<option value="{$i}" {if isset($profile) && $profile.shoe_size == "{$i}"}selected{/if}>{$i}</option>
							{/for}
						</select>
					</div>

					<div class="form-group">
						<label for="dress_size">Dress size</label>
						<input type="text" id="dress_size" name="dress_size" value="{if isset($profile.dress_size)}{$profile.dress_size}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="pant_size">Pant size</label>
						<input type="text" id="pant_size" name="pant_size" value="{if isset($profile.pant_size)}{$profile.pant_size}{/if}" class="form-control input-sm" />
					</div>

					<div class="form-group">
						<label for="shirt_size">Shirt Size</label>
						<select id="shirt_size" name="shirt_size" class="select2 form-control input-sm">
							<option></option>
							{foreach $shirtSizes as $value => $display}
								<option {if isset($profile) && $profile.shirt_size == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="education">Education</label>
						<select id="education" name="education" class="select2 form-control input-sm">
							<option></option>
							{foreach $educationTypes as $value => $display}
								<option {if $profile.education == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="content_rating">Content Rating</label>
						<select id="content_rating" name="content_rating" class="select2 form-control input-sm">
							<option></option>
							{foreach $contentRatings as $value => $display}
								<option {if $profile.content_rating == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group span6">
						<label for="content_rating">Brands</label>
						<textarea class="form-control">{if isset($profile.brands)}{$profile.brands}{/if}</textarea>
					</div>

				</div>

				<div class="col-xs-4">

					<div class="form-group">
						<label for="pant_waist">Pant Waist</label>
						<select id="pant_waist" name="pant_waist" class="select2 form-control input-sm">
							<option></option>
							{for $i=27 to 44}
								<option value="{$i}" {if isset($profile.pant_waist) && $profile.pant_waist == "{$i}"}selected{/if}>{$i}</option>
							{/for}
						</select>
					</div>

					<div class="form-group">
						<label for="pant_length">Pant Length</label>
						<select id="pant_length" name="pant_length" class="select2 form-control input-sm">
							<option></option>
							{for $i=27 to 40}
								<option value="{$i}" {if isset($profile.pant_length) && $profile.pant_length == "{$i}"}selected{/if}>{$i}</option>
							{/for}
						</select>
					</div>

					<div class="form-group">
						<label for="ethnicity">Ethnicity</label>
						<select id="ethnicity" name="ethnicity" class="select2 form-control input-sm">
							<option></option>
							{foreach $enthicityTypes as $value => $display}
								<option {if $profile.ethnicity == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="eye_color">Eye Color</label>
						<select id="eye_color" name="eye_color" class="select2 form-control input-sm">
							<option></option>
							{foreach $eyeColors as $value => $display}
								<option {if isset($profile.eye_color) && $profile.eye_color == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="hair_color">Hair Color</label>
						<select id="hair_color" name="hair_color" class="select2 form-control input-sm">
							<option></option>
							{foreach $hairColors as $value => $display}
								<option {if isset($profile.hair_color) && $profile.hair_color == $value}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="glasses" value="false" />
							<input type="checkbox" id="glasses" name="glasses" value="true" {if isset($profile.glasses)}{if $profile.glasses}checked{/if}{/if} /> Glasses
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="travel" value="false" />
							<input type="checkbox" id="travel" name="travel" value="true" {if isset($profile.travel)}{if $profile.travel}checked{/if}{/if} /> Travel
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="passport" value="false" />
							<input type="checkbox" id="passport" name="passport" value="true" {if isset($profile.passport)}{if $profile.passport}checked{/if}{/if} /> Passport
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="drivers_license" value="false" />
							<input type="checkbox" id="drivers_license" name="drivers_license" value="true" {if isset($profile.drivers_license)}{if $profile.drivers_license}checked{/if}{/if} /> Drivers License
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="married" value="false" />
							<input type="checkbox" id="married" name="married" value="true" {if isset($profile.married)}{if $profile.married}checked{/if}{/if} /> Married
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="children" value="false" />
							<input type="checkbox" id="children" name="children" value="true" {if isset($profile.children)}{if $profile.children}checked{/if}{/if} /> Children
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="drink" value="false" />
							<input type="checkbox" id="drink" name="drink" value="true" {if isset($profile.drink)}{if $profile.drink}checked{/if}{/if} /> Drink
						</label>
					</div>

					<div class="checkbox">
						<label>
							<input type="hidden" name="pet" value="false" />
							<input type="checkbox" id="pet" name="pet" value="true" {if isset($profile.pet)}{if $profile.pet}checked{/if}{/if} /> Pet
						</label>
					</div>

					<div class="form-group">
						<label for="pet_type">Pet Type</label>
						<select id="pet_type" name="pet_type[]" class="select2 form-control input-sm" multiple>
							{foreach $petTypes as $value => $display}
								<option value="{$value}" {if isset($profile.pet_type[$value]) && $profile.pet_type[$value]}selected{/if}>{$display}</option>
							{/foreach}
						</select>
					</div>

					<div class="form-group">
						<label for="phone_type">Phone Type</label>
						<select id="phone_type" name="phone_type" class="select2 form-control input-sm">
							<option></option>
							{foreach $phoneTypes as $value => $display}
							<option {if isset($profile.phone_type) && $profile.phone_type == $value}selected{/if}>{$display}
								{/foreach}
						</select>
					</div>

				</div>
			</div>

			<input type="hidden" name="email" value="{$profile.email}" />

			<div class="form-group text-center">
				<button id="saveBtn" type="submit" class="btn btn-default cyan-outline-btn">Save Changes</button>
			</div>

		</div>
	</section>
</form>
