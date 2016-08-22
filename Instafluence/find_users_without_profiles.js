db.user.find().forEach(function(o){if(db.profile.find({email:o.email}).length() == 0){print(o.email);};})
