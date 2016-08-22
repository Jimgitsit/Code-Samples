db.user.find().forEach(function(o){
	if (db.profile.find({email:o.email}).length() == 0) {
		print("Creating profile for " + o.email);
		db.profile.insert({
			email: o.email,
			tier: "1",
			created: new Date(),
			initial_total_followers: "0"
		});
	};
});