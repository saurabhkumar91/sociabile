<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class SettingsController 
{ 
    
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function generateOTPAction($header_data,$post_data)
    { 
        try {
            if( !isset($post_data['type'])) {
                Library::logging('alert',"API : generateOTP : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_INPUT, null);
            } else {
                $user = Users::findById($header_data['id']);
                if($post_data['type'] == 1) { // for change mobile no
                    if( !isset($post_data['mobile_no'])) {
                        Library::logging('alert',"API : generateOTP : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_INPUT, null);
                    } else {
                        if($user) {
                            $mobileNo   = Users::find(array( array("mobile_no"=>$post_data['mobile_no']) ));
                            if( !empty($mobileNo[0]) && ($mobileNo[0]->is_deleted == 0) ){
                                Library::logging('alert',"API : generateOTP : mobile no exists : user_id : ".$header_data['id']);
                                Library::output(false, '0', "This mobile no already exists.", null);
                            }
                            $user->change_mobile_no = $post_data['mobile_no'];
                            $user->otp = 1234;

                            if ($user->save() == false) {
                                foreach ($user->getMessages() as $message) {
                                    $errors[] = $message->getMessage();
                                }
                                Library::logging('error',"API : generateOTP, error_msg : ".$errors." : user_id : ".$header_data['id']);
                                Library::output(false, '0', $errors, null);
                            } else {
                                $result['otp'] = 1234;
                                Library::output(true, '1', "OTP Sent Successfully", $result);
                            }
                        } else {
                             Library::output(false, '0', USER_NOT_REGISTERED, null);
                        }
                    }
                } elseif ($post_data['type'] == 2) { // for change password
                     if( !isset($post_data['email_id'])) {
                        Library::logging('alert',"API : generateOTP : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_INPUT, null);
                    } else {
                        if($user) {
                            $emails = array();
                            array_push($emails,$post_data['email_id']);
                            $user->otp = 1234;
                            $user->email_id = $emails;

                            if ($user->save() == false) {
                                foreach ($user->getMessages() as $message) {
                                    $errors[] = $message->getMessage();
                                }
                                Library::logging('error',"API : generateOTP, error_msg : ".$errors." : user_id : ".$header_data['id']);
                                Library::output(false, '0', $errors, null);
                            } else {
                                $message = "Your OTP is : 1234";
                                Library::sendMail( $post_data['email_id'], $message, "Forgot Password | Sociabile" );
                                Library::output(true, '1', "OTP Sent Successfully", null);
                            }
                        } else {
                             Library::output(false, '0', USER_NOT_REGISTERED, null);
                        }
                    }
                } else {
                    Library::logging('error',"API : changeNumber :, error_msg : wrong type, user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            }
            
        } catch(Exception $e) {
            Library::logging('error',"API : changeNumber : ".$e." user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function changeNumberAction($header_data,$post_data)
    {   
        if( !isset($post_data['otp_no'])) {
            Library::logging('alert',"API : changeNumber : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
             try {
                 $user = Users::findById($header_data['id']);
                 if($user->otp == $post_data['otp_no']) {
                    $old_mobile_no = $user->mobile_no;
                    $new_mobile_no = $user->change_mobile_no;
                    $user->mobile_no = $new_mobile_no;
                    $user->change_mobile_no = $old_mobile_no;
                    if ($user->save() == false) {
                       foreach ($user->getMessages() as $message) {
                           $errors[] = $message->getMessage();
                       }
                       Library::logging('error',"API : changeNumber : ".$errors." : user_id : ".$header_data['id']);
                       Library::output(false, '0', $errors, null);
                   } else {
                       Library::output(true, '1', CHANGE_NUMBER, null);
                   }
                 } else {
                     Library::logging('alert',OTP_WRONG." ".": user_id : ".$header_data['id']);
                     Library::output(false, '0', OTP_WRONG, null);
                 }
                 
            } catch(Exception $e) {
                Library::logging('error',"API : changeNumber, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * @param object request params
     * @param object reponse object
     *
     * @author Saurabh Kumar
     * @return json
     */
    
    public function helpAction( $header_data, $type )
    {  
        try {
            $aboutSociabile = array(
                array(
                    "question"=>"What is Sociabile", 
                    "answer"=>  "Sociabile is a portmanteau of the words “Mobile” and “Social” resulting in the word Sociabile. Sociabile is your private, mobile, social network. Our goal is to block out the noise and help you share with just the people you care about. Sociabile is designed to help you protect your privacy and control who you can join your network and the information they are able to see."
                    ),
                
                array(
                    "question"=>"Why use Sociabile" , 
                    "answer"=>    "There is a trend where people are leaving the open and public social networks and moving toward chatting applications that are more personal and private. Sociabile is bridging the gap because using social networking and maintaining privacy. When reading about each feature you can get an idea of how we designed our network to help you control your privacy easily and seamlessly."
                    ),
                
                array(
                    "question"=>"Marketing", 
                    "answer"=>  "Sociabile is dedicated to providing an ad-free environment. We do not wish to subject our users to marketing campaigns or provide your personally identifiable information to any third party."
                    ),
                
                array(
                    "question"=>"Does it cost money?"   , 
                    "answer"=>   "No! Sociabile is a free service that allows you to connect to the people that are close to you. Sociabile does contain in-app purchases for animated emoticons if you wish to purchase them for use with Sociabile services."
                    ),
                
                array(
                    "question"=>"Registration"  , 
                    "answer"=>    "Registration is simple and free. Once you download Sociabile you will be required to enter your phone number for verification. Each phone number can be used only once and is required to verify our users. We want to limit the use of fake profiles and protect our users from spamming. Once you have verified your phone number you may start using Sociabile. See the “About Friend Connections” to learn how to connect to your friends."
                    ),
                
                array(
                    "question"=>"Password protection"   , 
                    "answer"=>    "By default Sociabile does not require a password to login. Your account is tied to your phone and phone number used to register. Therefore, your account can only be accessed on the mobile device used for registration. If you wish to have further security you may add a password to your account through the settings tab. This will require you to login each time you open the application."
                    ),
                
                array(
                    "question"=>"I Deleted the application" , 
                    "answer"=>    "If you deleted the application and wish to recover your account you can do so by reinstalling the application. After installing the app, enter your phone number to register. It will prompt you that this number has already been registered and direct you to the account recovery system. If you registered your email to your account then you can recover your account by entering your email. If you deleted your account prior to deleting the application then you will not be able to recover your account. "
                    ),
                
                array(
                    "question"=>"I Got a new phone" , 
                    "answer"=>    "Install Sociabile on your new mobile device and enter your phone number to register. It will prompt you that this number has already been registered and direct you to the account recovery system. If you registered your email to your account then you can recover your account by entering your email."
                    ),
                
                array(
                    "question"=>"I Changed my phone number" , 
                    "answer"=>    "If you change your phone number then we recommend going to the Setting Tab and updating your number using through the Account Information Screen. Having your current number and email registered will help recover your application if you ever delete Sociabile or get a new phone. If you delete your account then it will not be recoverable."
                    ),
                
                array(
                    "question"=>"Deleting My Account"   , 
                    "answer"=>    "If you wish to leave Sociabile you may delete your account by going to Account Information under the settings tab. Once your account has been deleted it will not be recoverable. Make sure you have backed up any information you wish to save before deleting your account."
                    )
            );
            
            $aboutMyProfile = array(
                array(
                    "question"=>"Profile Information"   , 
                    "answer"=>    "Your profile displays your name, a context indicator and your date of birth. Your name is entered when registering for Sociabile and can be updated by clicking the edit button on the profile page. Birthday can be updated through the edit option and will only display the month and day on your profile."
                    ),
                
                array(
                    "question"=>"Profile Privacy"   , 
                    "answer"=>    "Only users whom you have made a connection with can view your profile. Once you connect with a user you will need to add them to a group that can be used to control access to various elements on your profile such as My Mind messages, About Me content and My Pictures."
                    ),
                
                array(
                    "question"=>"Context Indicator" , 
                    "answer"=>    "The context indicator displays your availability to other users when they view your profile. It lets other know if your available, busy or performing various other action. You may select from the predefined options or create your own custom context indicator."
                    ),
                
                array(
                    "question"  =>  "My Mind"   , 
                    "answer"=>  "My Mind allows you to post messaged on your profile displaying what you are thinking at that time. Other users who have been given permission can view your My Mind messages and leave comments, like, or dislike the message."
                    ),
                
                array(
                    "question"=>"My Mind Privacy"   , 
                    "answer"=>    "My Mind messages can only be seen by users whom you have made contact with and are given permission to view this content. Users are given permission by being put in groups that you have given permission to view the content."
                    ),
                
                array(
                    "question"=>"About Me"  , 
                    "answer"=>    "About me tab of your profile contains general information about you for others to read whom you have connected with. Only users who have been given permission can view your About Me information."
                    ),
                
                array(
                    "question"=>"About Me Privacy"  , 
                    "answer"=>    "Information on your About Me page can only be seen by users whom you have made contact with and are given permission to view this content. Users are given permission by being put in groups that you have given permission to view the content."
                    ),
                
                array(
                    "question"=>"My Pictures"   , 
                    "answer"=>    "The My Pictures section contains images that you have uploaded to your profile. User who have been given permission can view your pictures and leave comments, like or dislike the picture. You may also share your pictures with other users and have your image appear in their Shared Images tab. Images that are shared can be seen by the person you have shared it with regardless of your privacy settings."
                    ),
                
                array(
                    "question"=>"My Pictures Privacy"   , 
                    "answer"=>    "Pictures that you upload can only be seen by users whom you have made contact with and are given permission to view this content. Users are given permission by being put in groups that you have given permission to view the content."
                    ),
                
                array(
                    "question"=>"Shared Pictures"   , 
                    "answer"=>    "Shared pictures are images that other users you have connected with shared with you. When another user chooses to share a picture with you, that picture will appear in your Shared Pictures tab. Only you can see images that appear in this tab. Other users cannot see images that have been shared with you appearing in your Share Images section. Your Shared Images tab is private and only visible by you."
                    )
                
            );

            $aboutMyMindMessages    =   array(
                array(
                    "question"=>"Creating a My Mind Message"    , 
                    "answer"=>    "My Mind messages are used to publically display what is on your mind for others to see. Only those who have been granted permission can view these messages. To create a new My Mind message, go to the “My Profile” tab. You will see a text box that says “What’s on your mind?”. Type your message here and click the send button."
                    ),

                                array(
                    "question"=>"Deleting a My Mind Message"    , 
                    "answer"=>    "To delete a My Mind message that you have posted, swipe your finger across the message you wish to delete. A DELETE button will appear allowing you to delete the My Mind post from your profile."),

                array(
                    "question"=>"Privacy Settings for My Mind Messages" , 
                    "answer"=>    "Privacy settings for My Mind messages can be set under the Privacy Settings   My Mind options from the Settings tab. From there, you can select which groups have permission to view your My Mind messages. This will be the default settings for all My Mind message you post. You may also create custom privacy settings for individual My Mind messages. See “Custom Privacy Settings” for instructions."
                    ),

                                array(
                    "question"=>"Custom Privacy Settings"   , 
                    "answer"=>    "To create custom privacy settings for an individual My Mind message, click on the message to view it. In the top right corner there will be a lock. Click on the lock and you can select the groups or individuals you want to give permission to view this specific My Mind message. The default settings will not be used when custom settings are declared."),

                array(
                    "question"=>"Comments/Likes/Dislikes"   , 
                    "answer"=>    "Each My Mind message will display the number of comments, likes, and dislikes for that message. To view these, click on the My Mind message and the comments will appear. From the comment page, you can click the “View Likes/Dislikes” link to view who has liked or disliked your My Mind Message. You may also leave a comment, like or dislike the My Mind message from this page."
                    )
            );
            
            $aboutPictures  = array(
                array(
                    "question"=>                "Adding a new image"    , 
                    "answer"=>    "To add a new image to your profile, go to the My Images section under the My Profile tab. In the top right corner, there will be a plus sign that is used to add new images from your mobile device’s photo album."
                    ),
                
                array(
                    "question"=>"Set as profile image"  , 
                    "answer"=>    "There are three ways to set your profile picture. The first way is to click on the image under then My Profile tab. This will allow you to take a picture or select one from your mobile device’s photo album. The second way is to click on the image under Account Information that appears in the settings tab. This will also allow you to take a picture or select one from your mobile device’s photo album. The third way is to select an image from your My Photos section, click on the image top view it. In the bottom right corner of the screen there are three dots with more options. One option will be to set this image as your profile image."
                    ),
                
                array(
                    "question"=>"Deleting an image" , 
                    "answer"=>    "To delete an image, go to the My Photos section under the My Profile tab. Click on the image you wish to delete. In the bottom right corner of the screen there are three dots with more options. Select the option that says “Delete Image” to delete the image. If this image was shared with others then it will no longer be available in the Shared Pictures section of the people it was shared with."
                    ),
                
                array(
                    "question"=>"Delete Profile Image"  , 
                    "answer"=>    "To delete you profile image, click on the image under the My Profile tab or click on the image in the Account Information section of the Settings tab. A menu option will appear, select “Delete Photo” and the image will be removed."
                    ),
                
                array(
                    "question"=>"Shared Pictures"   , 
                    "answer"=>    "Shared pictures are images that other users you have connected with shared with you. When another user chooses to share a picture with you, that picture will appear in your Shared Pictures tab. Only you can see images that appear in this tab. Other users cannot see images that have been shared with you appearing in your Share Images section. Your Shared Images tab is private and only visible by you."
                    ),
                
                array(
                    "question"=>"Sharing an image"  , 
                    "answer"=>    "Any image you share with another use will appear in their Shared Images section. These images can only be viewed by the people you share them with or friends who have permission to view the image on your profile. If a user belongs to a group that does not have permission to view your images, then the image shared will appear in their Shared Images section but not viewable in your My Pictures section due to privacy settings. To share an image, select the image you wish to share. In the bottom right corner of the screen there are three dots with more options. One option will be to Share the image. Click this option and select the users you wish to share the image with."
                    ),
                
                array(
                    "question"=>"Privacy Settings for images"   , 
                    "answer"=>    "Privacy settings for My Pictures can be set under the Privacy Settings   My Pictures options from the Settings tab. From there, you can select which groups have permission to view your pictures. This will be the default settings for all pictures you post. You may also create custom privacy settings for individual pictures. See “Custom Privacy Settings” for instructions."
                    ),
                
                array(
                    "question"=>"Custom Privacy Settings"   , 
                    "answer"=>    "To create custom privacy settings for an individual picture, click on the picture to view it. In the bottom right corner of the screen there are three dots with more options. Click the Privacy Settings option and you can select the groups or individuals you want to give permission to view this specific image. The default settings will not be used when custom settings are declared."
                    )
                
            );
            
            $aboutFriendConnections = array(
                array(
                    "question"=>"How to connect with friends"   , 
                    "answer"=>    "There are two ways to connect to friends. The first is based on your phone number being in your friend’s phone or your friend’s phone number being stored in your phone’s address book. Sociabile can recommend friends based on the phone numbers in your contact list. The second way is by searching for your friend’s specific userID. See “Recommended friends” or “Searching for friends” for more information. Once you add a friend, you will need to designate the group they will belong to once the friend request is accepted."
                    ),
                
                array(
                    "question"=>"My UserID" , 
                    "answer"=>    "Your UserID is a unique value use to search for your profile. You are assigned a random userID when you join Sociabile. You may then change your userID by going to Account Information under the settings tab. The userID you select must be unique (not used by another user) and can only be changed once. You may give this userID to your friends so they can search for you and add you as a friend on Sociabile."
                    ),
                
                array(
                    "question"=>"Recommended friends"   , 
                    "answer"=>    "Friends will be recommended to you based on the phone numbers in your mobile device’s contact list. If a phone number in your contact list is registered to Sociabile, then the user that it is registered to will appear in your recommended friends list. You may add users from the recommended friends list or Hide users so that they do not appear."
                    ),
                
                array(
                    "question"=>"Hide/Unhide Friends"   , 
                    "answer"=>    "If someone appears in your recommended friends list, you may add them, leave the recommendation as pending by doing nothing, or Hide the user so that they no longer appear in the recommended friends list. If you Hide a recommendation and would like to Unhide it later so that you can add that friend, you can do so through the Hidden Friends option in Account Information under the Settings Tab."
                    ),
                
                array(
                    "question"=>"Searching for friends" , 
                    "answer"=>    "You may search for friends to add by clicking on the magnifying glass at the top of the Friends tab. You may only search based on the userID. A single result will be displayed for a successful search based on the userID. If you wish to give your userID to a friend so they can search and add you, you can find your userID in Account Information under the Settings Tab."
                    ),
                
                array(
                    "question"=>"Friend Requests"   , 
                    "answer"=>    "Other Socialites may add you by searching for your userID that you provided them or by having your phone number stored in their mobile device. When another user adds you a request will appear in the Request section under the Friends tab. You may accept or reject the request. If you accept the friend request then you will need to add the new connection to a friend group."
                    ),
                
                array(
                    "question"=>"Friend Groups" , 
                    "answer"=>    "Sociabile is dedicated to providing users a social network focused on privacy. Friend groups are used to control the privacy of your information. Each person you connect to on Sociabile must be placed in one or more groups. These groups are used to give permission to content on Sociabile (i.e. My Mind Messages, About Me, My Pictures)."
                    ),
                
                array(
                    "question"=>"Create Custom Groups"  , 
                    "answer"=>    "There are several predefined groups when you join Sociabile (i.e. Friends, Family, Acquaintances, etc.). You may add users to these groups or create your own custom groups. To create a custom group, go to Privacy Settings under the Settings tab. There are three options for setting your default privacy settings: My Mind, About Me, My Pictures. Under any of these three options you will see all the existing groups and a link at the top that says “Add New Group.” Clicking this link will allow you to add a new group. Once the group is created, you may start adding friends to that group."
                    ),
                
                array(
                    "question"=>"Deleting a Custom Group"   , 
                    "answer"=>    "In the Privacy Settings menu option under the Settings Tab, you can click on any of the three options available (My Mind, About Me, My Pictures) to see all the groups you have on Sociabile. You may swipe your finger across any of the custom groups you created and a Delete button will appear allowing you to delete the custom group. Predefined groups cannot be deleted. If a user belongs to the deleted group and another group, then they will remain in the other groups and the delete group will no longer exist. If a user is only associated with the group that is deleted, leaving them without a group, then they will be automatically added to the acquaintance group. All users must be associated with at least one group."
                    ),
                
                array(
                    "question"=>"Deleting a Friend Connection"  , 
                    "answer"=>    "If you wish to remove a friend from your Sociabile account, you may do so by going to the Friends tab and swiping across the friend’s name. This will display a Delete button that will allow you to remove the friend from your list. You will no longer appear on your friend’s list once the connection has been deleted."
                    )
                
            );
            
            $aboutChat  = array(
                array(
                    "question"=>"Create New Chat"   , 
                    "answer"=>    "To create a new chat conversation, select the person’s name you wish to chat with from the Friends Tab. This will open a mini-description containing the user’s name, image, context indicator, two command buttons and a star indicating if you have added this person to the favorite list. Learn more about Context Indicators and Favorites below. Click the “Message” button to begin the chat conversation. You may also use the + sign under the Chat Tab and select and individual to chat with or start a group chat."
                    ),
                
                array(
                    "question"=>"Context Indicator" , 
                    "answer"=>    "Context indicators let you know someone’s availability to chat. They are set on the profile page and viewable from the Chat tab when you click on a person’s name."
                    ),
                 
                array(
                    "question"=>"Favorite List" , 
                    "answer"=>    "The Favorite list helps you connect to those you talk to most by putting their names at the top of your friends list. Favorites is not a group, it’s just a category displaying the names of those in the Friends tab that you have selected to show at the top. It does not affect groups or any privacy settings throughout the app. To add someone to the favorite list, select the person you wish to Favorite from the Friends Tab and click on the star under their mini profile. Click the star again to remove them from the favorites list."
                    ),
                
                array(
                    "question"=>"Create Group Chat" , 
                    "answer"=>    "To create a group chat, click the + in the top right corner of the Chat Tab and select the friends you wish to participate in the group chat."
                    ),
                
                array(
                    "question"=>"Emoticons" , 
                    "answer"=>    "Emoticons are a great way to enhance the chatting experience and express emotions that are typically triggered through non-verbal cues in real life conversations. Let the characters express these emotions for you. To learn more about emoticons, view the Emoticons section."
                    ),
                
                array(
                    "question"=>"Delete Chat Conversation"  , 
                    "answer"=>    "To delete a chat conversation, swipe across the conversation you with to delete. This will display the Delete button that can be used to remove this conversation from you application. Deleting a conversation will make it no longer visible to you, but the other parties involved in the conversation will still be able to see messages sent until they have deleted the conversation from their application."
                    ),
                
                array(
                    "question"=>"What is a Time Capsule?"   , 
                    "answer"=>    "A Time Capsule in Sociabile is a new way to send messages to your friends that can only be viewed as the specific date and time designated by the sender. A time capsule may contain text and images that are viewable by the receiver(s) when it is designated to be opened. The sender and message remain anonymous until the open date of the time capsule. This is a good way to record memories and revisit them at a future date and time that you designate."
                    ),
                
                array(
                    "question"=>"Create New Time Capsule"   , 
                    "answer"=>    "To create a new time capsule, click the Time Capsule link under the Chat Tab. This will display any time capsules that you have received. At the top of the screen, click the + sign to create a new time capsule. You may then enter the message, images, recipients of the time capsule and set the date and time that it will open."
                    ),
                
                array(
                    "question"=>"Open a Time Capsule"   , 
                    "answer"=>    "A time capsule can only be opened at the specific date and time that has been set for the time capsule. Time capsules that are not ready to open will display the icon with the date and time. All capsules that are ready to open will appear at the top. Click on the time capsule you wish to open to read the contents."
                    ),
                
                array(
                    "question"=>"Delete a Time Capsule" , 
                    "answer"=>    "A time capsule can only be deleted after it has been opened. Pending time capsules cannot be deleted. To delete a time capsule that has already been opened, swipe across the time capsule you wish to delete. This will display the Delete button that can be used to remove the time capsule from you application. Deleting a time capsule will make it no longer visible to you, but the other recipients of the time capsule will still be able to view its contents until they have deleted the time capsule from their application."
                    )
                
            );
            
            $emoticons  = array(
                array(
                    "question"=>"What are emoticons?"   , 
                    "answer"=>    "Emoticons are characters that can be used to enhance your chatting experience. These characters express emotions for you that are often lost due to the absence of non-verbal cues you typically have in person. They add life to the chatting experience making it more fun and entertaining. They may be static or animated."
                    ),
                
                array(
                    "question"=>"Static Emoticons"  , 
                    "answer"=>    "Static emoticons are characters that do not have action. They possess a single pose or facial expression used to illustrate the mood desired."
                    ),
                
                array(
                    "question"=>"Animated Emoticons"    , 
                    "answer"=>    "Animated emoticons bring your chat to life with characters performing actions to enhance the chatting experience. They are typically 1-3 second animations representing situations or emotions that can be expressed while chatting rather than described."
                    ),
                
                array(
                    "question"=>"Using Emoticons"   , 
                    "answer"=>    "To use emoticons, click on the smiley face that appears next to the text box and above the keypad while chatting. This will replace your keypad with a list of emoticons to choose from. There is an assortment of static emoticons that are provided to you when you join Sociabile. Animated emoticons may be purchases from the Emoticon store and used during the chat. Purchased emoticons will appear as an emoticon set and grouped by tabs at the bottom of the emoticon window. Select the tab containing emoticons you want to use and scroll through the option. After choosing the emoticon you wish you use, click the Send button to send it to the person you are chatting with."
                    ),
                
                array(
                    "question"=>"Purchase Emoticons"    , 
                    "answer"=>    "To purchase emoticons, visit the Emoticons section under the Settings Tab. This will display all the emoticons currently available in the emoticons store. These will be purchased as an in-app purchase through Sociabile. Click on the Emoticon set you wish to purchase and click the “Purchase Emoticon” button. Once the purchase is made you may download your emoticon set and start using it."
                    ),
                
                array(
                    "question"=>"Recover Purchased Emoticons"   , 
                    "answer"=>    "If you have already purchased an emoticons set and it does not appear, you may download the Emoticon set again through the Emoticons section under the Settings Tab without having to pay again. If you delete Sociabile and download again, or purchase a new phone, you will need to use this option to recover Emoticons you previously purchased. Click the “More Option” button at the top right corner of the screen in the Emoticon store. This will display emoticons you have purchased and let you order the tabs in your chat window. There is an option that says “Purchased” where you can download emoticon sets that have already been paid for. Click this option and select Download to recover your previous purchase."
                    )
            );
            
            $aboutSocial =   array(
                array(
                    "question"=>"What is the Social Tab"    , 
                    "answer"=>    "The Social Tab is like the water cooler where you can catch up on happenings from your friends. My Mind messages and images that your friend connections have made will appear here so that you can see what recent activities have taken place. You may filter the results in the Social tab based on groups of friends."
                    ),
                
                array(
                    "question"=>"How to filter Sociable Results"    , 
                    "answer"=>    "To filter the results of the Social Tab based on groups, tap on the Select Audience option at the top of the screen. This will display all the groups you have created. The groups that are checked will have social events appear in the Social Tab. Tap on the group to either check, or uncheck them to manage what information shows up in your Social Events. Only recent posts by members of the selected groups will appear in the Social Tab."
                    )

            );
                    
            switch ( $type ) {
                case 1:
                    $result    = $aboutSociabile;
                    break;
                case 2:
                    $result    = $aboutMyProfile;
                    break;
                case 3:
                    $result    = $aboutMyMindMessages;
                    break;
                case 4:
                    $result    = $aboutPictures;
                    break;
                case 5:
                    $result    = $aboutFriendConnections;
                    break;
                case 6:
                    $result    = $aboutChat;
                    break;
                case 7:
                    $result    = $emoticons;
                    break;
                case 8:
                    $result    = $aboutSocial;
                    break;
                default:
                    Library::logging('error',"API : help, invalid help type : ".$type." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                    break;
            }
            
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : help, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function aboutChatAction($header_data)
    {  
        try {
            $ques = array("How do a start a new chat?",
                            "How do I create a group chat?",
                            "How do I purchase emoticons?",
                            "How do I send emoticons?",
                            "Can I use normal emoticons?");
            $ans = array("Download the app",
                            "Create the group",
                            "Click on pay button",
                            "Click on pay button",
                            "yes"
                            );
            $result['ques'] = $ques;
            $result['ans'] = $ans;
            
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : aboutChat, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for get Category
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getCategoryAction($header_data)
    {
        try {
                $category = Category::find();
                $i = 0;
                $result = array();
                foreach($category as $cat) {
                    $result[$i]['id'] = (string)$cat->_id;
                    $result[$i]['name'] = (string)$cat->name;
                    $i++;
                }
                Library::output(true, '1', "No Error", $result);
            } catch(Exception $e) {
                Library::logging('error',"API : getCategory, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
        }
    
    }
    
    /**
     * Method for contact us API
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function contactUsAction($header_data,$post_data)
    {
        if( !isset($post_data['cat_id']) || !isset($post_data['message']) || !isset($post_data['email_id']) || !isset($post_data['user_device']) || !isset($post_data['device_model'])) {
            Library::logging('alert',"API : contactUs : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                
                /**** upload image ************************/
                if( !empty($_FILES["image"]['name']) && !empty($_FILES["image"]['tmp_name']) && empty($_FILES["image"]['error']) ){
                    $imgName    = rand().$_FILES["image"]['name'];
                    $uploadFile = $_FILES["image"]['tmp_name'];
                    $amazon     = new AmazonsController();
                    $amazonSign = $amazon->createsignatureAction($header_data,10);
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $ext        = explode(".", $imgName);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       =>  "profiles/".$imgName,//$amazonSign["key"],
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => file_get_contents($uploadFile)
                    );
                    $ch = curl_init();
                    $options = array(
                        CURLOPT_URL         => $url,
                        //CURLOPT_HEADER      => true,
                        CURLOPT_POST        => 1,
                        CURLOPT_HTTPHEADER  => $headers,
                        CURLOPT_POSTFIELDS  => $postfields,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true
                    ); // cURL options
                    curl_setopt_array($ch, $options);
                    $imageName              = curl_exec($ch);
                    curl_close($ch);
                    $post_data['image']     = FORM_ACTION.$imageName;
                }
            /**************************upload image code ends************************/                
                
                if( empty($post_data['image']) ){
                    $post_data['image'] = '';
                }   
                
                 $request = 'db.contact_us.insert({ 
                            user_id : "'.$header_data['id'].'",
                            cat_id: "'.$post_data['cat_id'].'", 
                            message: "'.$post_data['message'].'",
                            email_id: "'.$post_data['email_id'].'",
                            user_device: "'.$post_data['user_device'].'",
                            device_model: "'.$post_data['device_model'].'",
                            user_agent: "'.$_SERVER['HTTP_USER_AGENT'].'",
                            image: "'.$post_data['image'].'"
                    })';
                 
                $db = Library::getMongo();
                $result =  $db->execute($request);
                if($result['ok'] == 0) {
                    Library::logging('error',"API : contactUs, error_msg: ".$result['errmsg']." ".": user_id : ".$header_data['id']);
                }
                                
                Library::sendMail( CONTACT_US_EMAIL, $post_data['message'], "Contact Us" );
                
                Library::output(true, '1', "Post Sent Successfully.",null);
            } catch(Exception $e) {
                Library::logging('error',"API : contactUs, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for set password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function setPasswordAction($header_data,$post_data)
    {
        if( !isset($post_data['email_id']) || !isset($post_data['password'])) {
            Library::logging('alert',"API : setPassword : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $emails = array();
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                 array_push($emails,$post_data['email_id']);
                $user->email_id = $emails;
                $user->password = $security->hash($post_data['password']);
                if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : setPassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    Library::output(true, '1', SET_PASSWORD, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : setPassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * Method for reset password (forgot pass)
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function resetPasswordAction($header_data,$post_data)
    {
        if( !isset($post_data['otp_no']) || !isset($post_data['password'])) {
            Library::logging('alert',"API : resetPassword : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if($user) {
                    if($user->otp == $post_data['otp_no']) {
                        $user->password = $security->hash($post_data['password']);

                        if ($user->save() == false) {
                            foreach ($user->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            Library::logging('error',"API : resetPassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                            Library::output(false, '0', $errors, null);
                        } else {
                           Library::output(true, '1', SET_PASSWORD, null);
                        }
                    } else {
                        Library::logging('alert',OTP_WRONG." ".": user_id : ".$header_data['id']);
                        Library::output(false, '0', OTP_WRONG, null);
                    }
                } else {
                     Library::output(false, '0', USER_NOT_REGISTERED, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : resetPassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    
    /**
     * Method for delete password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function deletePasswordAction($header_data)
    {
        try {
            $user = Users::findById($header_data['id']);
            $user->password = '';
            if ($user->save() == false) {
                foreach ($user->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                Library::logging('error',"API : deletePassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
            } else {
                Library::output(true, '1', DELETE_PASSWORD, null);
            }
        } catch(Exception $e) {
            Library::logging('error',"API : deletePassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    /**
     * Method for change password
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function changePasswordAction($header_data,$post_data)
    {
        if( !isset($post_data['old_password']) || !isset($post_data['new_password'])) {
            Library::logging('alert',"API : changePassword : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if ($security->checkHash($post_data['old_password'], $user->password)) {
                    $new_password = $security->hash($post_data['new_password']);
                    $user->password = $new_password;
                    if ($user->save() == false) {
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                            Library::logging('error',"API : changePassword, error_msg : ".$errors." : user_id : ".$header_data['id']);
                            Library::output(false, '0', $errors, null);
                    } else {
                        Library::output(true, '1', SET_PASSWORD, null);
                    }

                } else {
                    Library::output(false, '0', INVALID_LOGIN, null);
                }
            } catch (Exception $e) {
                Library::logging('error',"API : changePassword, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    
    /**
     * Method for about sociabile
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function aboutSociabileAction($header_data,$type)
    {
        try {
            if($type == 1) {
                $message['terms'] = "<h3>Sociabile Terms of Service</h3><p><u>Welcome to Sociabile</u></p><p><i>Introduction to Sociabile</i></p><p>Thank you for choosing to use Sociabile. Sociabile is a portmanteau combining the words “Social” and “Mobile” to provide a social networking service based in a mobile environment. Sociabile is committed to providing a private social networking platform allowing you to control with whom you share information and includes many features that are designed to control who views your information. To view more information on how Sociabile takes your privacy seriously and helps you protect your information, view our Privacy Policy.</p><p><i>Introduction of Services</i></p><p>Sociabile provides a social networking platform where you may share content with other users who have been connected through the application. You may provide information on your profile, load images, or add My Mind messages to let others know what you are thinking. Only connected users with permission can view this information. You may comment, like, or dislike images or My Mind messages posted by connections made if proper permissions are granted. You may also share images with connections made. Any images shared will appear in the shared images tab under My Photos. A connected user can only view the My Shared photos that have been shared with him or her. To grant permission to connected users, upon establishing a connection you must categorize the connecting party into one or more groups that can be used to establish the privacy access level to your information.</p><p>Sociabile also provides chatting features allowing you to share private messages with individuals or groups with whom you have made a connection. Only the parties engaged in the chat conversation can view content exchanged such as text, images, and video. You may also create a time capsule to share information at a future date with connections made. You may send the time capsule to yourself or any other users who have been connected through the application. Time capsules may contain text, images, videos, or animated emoticons. The contents of the time capsule can only be viewed on the date and time that the time capsule has been set to open.</p><p><u><b>Terms of Service</b></u></p><p><i>1.1 Agreement to Terms of Services</i></p><p>This is an agreement between Sociabile LLC, the owners and operators of http://www.scble.com (collectively, the “Services”), and you, the user of the service. BY DOWNLOADING AND USING SOCIABILE SERVICES, YOU ACKNOWLEDGE AND AGREE TO SOCIABILE’S TERMS OF SERVICES, INCLUDING SOCIABILE'S PRIVACY POLICY WHICH ARE INCORPORATED HEREIN BY REFERENCE. You may not use these Services unless you agree to the Terms of Service.</p><p>Sociabile may provide links to third-party websites that are not owned or controlled by Sociabile. Sociabile has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third-party websites. In addition, Sociabile will not and cannot censor or edit the content of any third-party site. By using the Service, you expressly acknowledge and agree that Sociabile shall not be responsible for any damages, claims or other liability arising from or related to your use of any third-party website.</p><p><i>1.2 Who May Contract</i></p><p>Persons under 13 years of age may not contract to use or use Sociabile. Individuals under 18 years of age may only use Sociabile with consent from the user’s parent or legal guardian. Individuals may not use Sociabile on behalf of or for third parties, except if the third party is a business enterprise and the individual has a right to contract on behalf of the business enterprise. If an individual is using Sociabile on behalf of or for the purpose of a business enterprise, then the business enterprise as well as the individual users shall be deemed to have agreed to and accepted the Sociabile Terms of Services. By downloading and using Sociabile, you are acknowledging that you are of proper age and/or have consent to contract for the use of Sociabile.</p><p><i>1.3 Sociabile Access</i></p><p>Subject to your compliance with these Terms of Service, Sociabile hereby grants you permission to use the Service, provided that: (i) your use of the Service as permitted is solely for your personal use, and you are not permitted to resell or charge others for use of or access to the Service, or in any other manner inconsistent with these Terms of Service; (ii) you will not duplicate, transfer, give access to, copy or distribute any part of the Service in any medium without Sociabile's prior written authorization; (iii) you will not attempt to reverse engineer, alter or modify any part of the Service; and (iv) you will otherwise comply with the terms and conditions of these Terms of Service and Privacy Policy.</p><p><i>1.4 Revocation of Access</i></p><p>Sociabile reserves the right to revoke access to you at any time and without notice if Sociabile has reason to believe that you are promoting or participating in any actions that are deemed as prohibited under the Sociabile Terms of Service. A representative list of prohibited activities is set forth in Section 4 herein, but may be amended at any time by Sociabile by providing five calendar day notice to users. </p><p>You agree not to use or launch any automated system that accesses the Service in a manner that sends more request messages to the Sociabile servers in a given period of time than a human can reasonably produce in the same period by using a Sociabile application.  You agree not to reverse-engineer our system, our protocols, or explore outside the boundaries of the normal requests made by Sociabile clients, and you may not use any tools designed to explore or harm, penetrate or test the site. You agree not to spam, or solicit for commercial purposes, any users of the Service. Any such activities allow Sociabile to immediately revoke your access.</p><p><u>2. Using Sociabile</u></p><p><i>2.1 Introduction / User Account</i></p><p>Sociabile is a private social network based on connectivity using your mobile phone number or unique userID. Users of this application may be referred to as Socialites, which means a person who is prominent, well known, and fond of social activities. Your userID will be automatically assigned to you but may be changed based on your preferences. Your userID must be unique and can only be changed once. </p><p><i>2.2 Connecting to others</i></p><p>To connect to another Socialite, you must know the other user’s phone number or unique userID. If you have another member’s phone number in your mobile device address book, then that person will be recommended to you as a friend. You may add them and determine which group(s) you want them to be associated with. Groups are used to control privacy settings and all users must be in at least one group. You may also add users by searching for their unique userID that they have provided to you. The search will only return a single result if there is an exact match. This enables users to only connect with others they wish to and keep their network private. If you have been added by another user as a friend then you will receive a friend request allowing you to accept or reject that friend connection.</p><p><i>2.3 Groups</i></p><p>There are predefined groups when you first begin using Sociabile. You may add more groups to further define your friend connections. All users added to your network must be in a minimum of one group but may be added to multiple groups. If you create a custom group, then add members to the group, then delete the group, the members will no longer be part of the group. If a user that you connected to was added only to the custom group that was deleted, then they will be left without a group and by default be added to the predefined group called “Acquaintances.” </p><p><i>2.4 Privacy Settings</i></p><p>Privacy settings are used to provide permission to content rather than explicitly blocking users. Privacy settings are based on groups and can be maintained in the Settings Tab. You may grant permission to groups enabling them to view your “My Mind” messages, Profile information and Images that you posted. You can determine which groups you want to grant access to this information and all members who belong to those groups will be able to see the content generated. </p><p>Socialites may also create custom privacy settings for each individual My Mind message or Image posted. When posting a My Mind message or Image, click the customer privacy settings icon and select which groups or individual friends you want to grant permission. Creating custom settings will replace the default privacy settings established in the Settings Tab. Therefore, the default privacy settings will not be used and only users select in the custom settings will be able to see that My Mind message or Image.</p><p>Images may also be explicitly shared with other Socialites that you have connected to by clicking the Shared feature. Any images shared with you will appear in the “Shared Pictures” section on your profile. The Shared Pictures section of your profile is private and cannot be seen by other users. </p><p><i>2.5 Chat</i></p><p>Sociabile provides chatting services for users to engage in private conversations. A user may only send a chat request to other users that they have connected with through the application. Only members participating in the chat conversation can see messages sent between members of the chat. Chat conversations may consist of text, images, static emoticons, or animated emoticons. Images sent may be seen or downloaded only by individuals participating in the chat conversation.</p><p>Emoticons may be used to express emotions during a chat conversation. Emoticons can be either static or dynamic. Static emoticons are characters that do not move. They convey various expressions to enhance the chatting experience. Animated emoticons provide action to further show the emotion desired. All characters are trademarked either by Sociabile or a third party that has provided the emoticon for sale on Sociabile. Purchasing emoticons gives the user privilege to use the emoticons as long as they participate on Sociabile, but all rights and ownership remain with the trademark owner. </p><p><i>2.6 Time Capsules</i></p><p>Time capsules are a special type of messaging system offered through Sociabile allowing you to capture a moment in time or send a message that is intended for future use. Time capsules may include text, images and emoticons. Time capsules can only be sent to individuals that the sender has established a connection with or to themselves. Users who receive a time capsule may only open the time capsule on the date and time that it has been set to open. The message and sender can only be viewed once a time capsule has been opened to be read.</p><p><i>2.7 Account Recovery</i></p><p>We at Sociabile realize that our users may purchase new phones or change their phone number from time to time. To assist users with transferring their account to a new device or phone number, we have created the account recovery service. The account recovery service requires your email address to setup. In Account Information on the Setting Tab, you can select the e-mail account recovery system to add an email to your account. Your email will NOT be used for spam and is NOT required to use Sociabile. Its purpose serves to assist you in recovering your account in the event you delete the application and need to install it again on the original device or a new mobile device. Sociabile is committed to protecting your privacy and personal information.</p><p><u>3. Intellectual Property Right</u></p><p>Sociabile respects your privacy and intellectual property rights. We are committed to providing a service that you may use for both business and personal use. Users will retain ownership and intellectual property rights of any content sent through Sociabile’s services. Furthermore, Sociabile will never provide personally identifiable information or intellectual property of our users to a third party except as may be required by law.</p><p>By adding content to the Sociabile site, you agree that you have acquired all intellectual property rights to the content and will fully indemnify Sociabile for any claim of infringement brought by third-parties.</p><p>The design of the Sociabile Service along with Sociabile created text, scripts, graphics, interactive features, and the trademarks, service marks and logos contained therein ('Marks'), are owned by or licensed to Sociabile, subject to copyright and other intellectual property rights under United States and foreign laws and international conventions. The Service is provided to you AS IS for your information and personal use only. Sociabile reserves all rights not expressly granted in and to the Service. You agree to not engage in the use, copying, or distribution of any of the Service other than expressly permitted herein, and are expressly prohibited from using the Service for any commercial purposes.</p><p>Sociabile is dedicated to constant improvement of its services to the community of users. It is our belief that the most valuable information for improvement comes from our users. If you (the user) posts any content on our Services, you are deemed to have granted permission to Sociabile and its partners to use, store, copy, modify, transmit, disclose, distribute, or create derived work. This license will continue perpetually even if the user ceases to use Sociabile or deactivates their account. To recapitulate this agreement, Sociabile and its partners may use content provided for the advancement of its services and will not divulge any personally identifiable information or intellectual property to a third party.</p><p><u>4. Prohibited Activities</u></p><p>Users may be removed from the Sociabile community for conducting acts that are deemed as prohibited or unlawful. By using Sociabile you agree to the Terms of Services and will not engage in activities that are prohibited. If you identify other users engaging in such activities it should be reported to Sociabile in order to be investigated and determine if action needs to be taken. Below is a list of activities that is deemed as prohibited.</p><ul><li>Misrepresenting yourself or impersonating other. This includes the creation of fake profiles or profiles that misrepresent who you are to others.</li><li>Name squatting. Name squatting consists of creating a user name or userID that infringes on the intellectual property rights, privacy rights or other rights entitled to a third party.</li><li>Sending unsolicited messages or spamming users. Creating an account for the purpose of advertising or spamming is prohibited.</li><li>Using our services for commercial purposes or the benefit of a third party</li><li>Engaging in illegal activity as identified by US law (drugs, money laundering, prostitution, conspiracy, etc).</li><li>Disseminating violent or sexual material that may lead to discomfort of other users.</li><li>Engaging in discrimination of other members (including but not limited to: ethnic, culture, gender, religious, sexual or social status).</li><li>Engaging in bullying or the creation of a hostile environment for other users.</li><li>Gambling or enticing others to participate in gambling.</li><li>Using or exploiting our intellectual property or the intellectual property of our users.</li><li>Accessing our services or collecting data from our services without consent. This includes the use of bots, crawlers, or other software used to collect and store data.</li><li>Engaging in social engineering to manipulate members of our community.</li><li>Intentionally distributing viruses, malware or other harmful software that could cause harm to our services or computing devices of our users.</li><li>Creating multiple accounts for disruptive or abusive purposes.</li><li>Engaging in activities that encourages others to breach Sociabile Terms of Service.</li></ul><p>Users who have been convicted of crimes against children or committing an act of terrorism are prohibited from using our services. Children under the age of 13 are restricted from using Sociabile.</p><p><u>5. Advertising</u></p><p>We are committed to providing an Ad-Free social networking environment. We hate third party advertisement as much as anybody and will not be providing unsolicited advertisements, banner ads, or links by third party entities. Additionally, your personally identifiable information will not be sold to third parties for the use of advertising. We are committed to maintaining your privacy and protecting our users.</p><p><u>6. Animated Emoticons</u></p><p>Sociabile grants its users permission to use paid-for content through Sociabile services. Content such as Emoticons are solely for the purpose of use through services provided by Sociabile. The artist/third party that provides content for use through Sociabile will retain all intellectual property rights to the content they develop. Users acquire the right to use content through Sociabile services and are prohibited from use or redistributing content outside of Sociabile services.</p><p><u>7. In-App Payments</u></p><p>Sociabile is a free service to our community of users who have agreed to the Terms of Service. However, Sociabile users may need to make payments from time to time for additional services such as the use of animated emoticons. Users are responsible for providing appropriate funds for in-app purchases and the use of digital content. </p><p><u>8. Location-Based Services</u></p><p>Sociabile intends to provide location-based services for special features that will be provided in future versions. Location-based services may be turned off by the user at any time without consequence to basic services provided by Sociabile. By disabling location-based services, special features that will be added will not be utilized by that users.</p><p><u>9. Service Interruptions and Maintenance</u></p><p>Sociabile is committed to providing perpetual service without interruption every day of the year. However, there may be times where interruptions beyond Sociabile’s control occur or interruptions that are necessary for maintenance to maintain a high level of service quality or improve our services. When possible, users will be notified in advance if such interruptions are scheduled to occur. In the even that an unforeseen events occur that interrupt service, Sociabile will notify the users of the event and action taken immediately after the issue is resolved. Users do not have a right to any compensation for any service interruption time.</p><p><u>10. Termination of Services</u></p><p>You may choose to terminate your use of Sociabile at any time by going to the Account Information page under the Settings Tab. Usage statistics will be archived for performance and usage monitoring to evaluate Sociabile traffic and activity for improvement of services. Once you have deleted your account it will no longer be recoverable. Any pictures or text that have been shared with friends will be removed from their shared folder and no longer visible, and any emoticons will be deleted from your account. </p><p><u>11. Third-Party Research</u></p><p>Sociabile is open for active participation in academic research that may advance theoretical contributions or improve services provided to customers. When such research is conducted, no identifying information will be used or provided to third parties. Third party research will produce aggregate and statistical data for all published research to evaluate usage, effective, and generalizable behaviors. We will restrict any personally identifiable information from being used in any research conducted through Sociabile.</p><p><u>12. Warranty Disclaimer</u></p><p>YOU AGREE THAT YOUR USE OF THE SOCIABILE SERVICE SHALL BE AT YOUR SOLE RISK. TO THE FULLEST EXTENT PERMITTED BY LAW, SOCIABILE, ITS OFFICERS, DIRECTORS, EMPLOYEES, AND AGENTS DISCLAIM ALL WARRANTIES, EXPRESS OR IMPLIED, IN CONNECTION WITH THE SERVICE AND YOUR USE THEREOF. SOCIABILE MAKES NO WARRANTIES OR REPRESENTATIONS ABOUT THE ACCURACY OR COMPLETENESS OF THIS SERVICE'S CONTENT AND ASSUMES NO LIABILITY OR RESPONSIBILITY AND SHALL NOT BE LIABLE TO YOU WHETHER BASED ON WARRANTY, CONTRACT, TORT, OR ANY OTHER LEGAL THEORY, AND WHETHER OR NOT THE COMPANY IS ADVISED OF THE POSSIBILITY OF SUCH DAMAGES, FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, PUNITIVE, OR CONSEQUENTIAL DAMAGES WHATSOEVER RESULTING FROM ANY (I) ERRORS, MISTAKES, OR INACCURACIES OF CONTENT, (II) PERSONAL INJURY OR PROPERTY DAMAGE, OF ANY NATURE WHATSOEVER, RESULTING FROM YOUR ACCESS TO AND USE OF OUR SERVICE, (III) ANY UNAUTHORIZED ACCESS TO OR USE OF OUR SERVERS AND/OR ANY AND ALL PERSONAL INFORMATION AND/OR FINANCIAL INFORMATION STORED THEREIN, (IV) ANY INTERRUPTION OR CESSATION OF TRANSMISSION TO OR FROM OUR SERVICE, (V) ANY BUGS, VIRUSES, TROJAN HORSES, OR THE LIKE WHICH MAY BE TRANSMITTED TO OR THROUGH OUR SERVICE THROUGH THE ACTIONS OF ANY THIRD PARTY, AND/OR (VI) ANY ERRORS OR OMISSIONS IN ANY CONTENT OR FOR ANY LOSS OR DAMAGE OF ANY KIND INCURRED AS A RESULT OF THE USE OF ANY CONTENT POSTED, EMAILED, TRANSMITTED, OR OTHERWISE MADE AVAILABLE VIA THE SOCIABILE SERVICE. SOCIABILE DOES NOT WARRANT, ENDORSE, GUARANTEE, OR ASSUME RESPONSIBILITY FOR ANY PRODUCT OR SERVICE ADVERTISED OR OFFERED BY A THIRD PARTY THROUGH THE SOCIABILE SERVICE OR ANY HYPERLINKED WEBSITE OR FEATURED IN ANY USER SUBMISSION OR OTHER ADVERTISING, AND SOCIABILE WILL NOT BE A PARTY TO OR IN ANY WAY BE RESPONSIBLE FOR MONITORING ANY TRANSACTION BETWEEN YOU AND THIRD-PARTY PROVIDERS OF PRODUCTS OR SERVICES. </p><p><u>13. Liability for Negligence</u></p><p>While Sociabile takes measures to protect our users, we do not guarantee that our services are free of de facto or legal flaw. Users should use these services at their own risk and bear all responsibility for their actions. SOCIABILE SHALL NOT BE HELD RESPONSIBLE FOR DAMAGES INFLICTED UPON USERS THROUGH SOCIABILE WHILE USING OUR SERVICES. If an unlawful act occurs, it is the user’s responsibility to notify Sociabile so that we can take action to prevent them from occurring. It is the responsibility of the community to work together and prevent restricted or unlawful acts from occurring. By using Sociabile you are agreeing to these terms and using the services at your own risk. </p><p><u>14. Indemnification</u></p><p>You agree to defend, indemnify and hold harmless Sociabile, its parent corporation, officers, directors, employees and agents, from and against any and all claims, damages, obligations, losses, liabilities, costs or debt, and expenses (including but not limited to attorney's fees) arising from: (i) your use of and access to the Sociabile Service; (ii) your violation of any term of these Terms of Service; (iii) your violation of any third party right, including without limitation any copyright, property, or privacy right; or (iv) any claim that one of your submissions caused damage to a third party. This defense and indemnification obligation will survive these Terms of Service and your use of the Sociabile Service</p><p><u>15. The Terms of Service are Subject to Change</u></p><p>As our services continue to grow, Sociabile may make changes to the Terms of Service. When revisions are made to the Terms of Service or privacy policy, Sociabile will send notifications alerting our community of users about those changes. By continuing to use Sociabile services, you are deemed to have accepted the updated Terms of Service.</p><p>We welcome your comments and feedback about our Terms of Service as well as other inquiries/recommendations that may improve our service to you. Your feedback is important! The next section below covers feedback as it pertains to our Terms of Service.</p><p><u>16. Contacting Socialites</u></p><p>We may at times send messages to our users informing them of updates or other information. Messages sent to our user community will appear in the Notifications section under the Settings Tab. Users may be contacted in order to information them of updates, new emoticons that are available, solicit feedback or response to a survey administered by Sociabile or other reasons deemed necessary. </p><p>Users may also provide feedback or contact us through the help menu. In this event, a personal response may be given through email to the user if an email is provided. Upon termination of service by the user, we may contact the user to inquire about their reasons for leaving the Sociabile community so that we may improve services to better serve our users.</p><p><u>17. Contacting Sociabile</u></p><p>User may contact us at Sociabile to provide feedback, report a problem, give a recommendation or submit an inquiry. Using the Help Menu under the settings Tab, the can select the contact option to send a message to Sociabile. The message may include text and an image. We want to hear from our users and will make every attempt to respond to all inquiries that need attention in a timely manner. However, a response is not guaranteed depending on the type of inquiry or volume of inquiries.</p><p>When submitting an inquiry through the mobile application, we will collect information to help us understand the inquiry or resolve any potential issues reported. User provide their intended message, email address (optional) and image (optional). Information about the user’s mobile device and model, operating system, current version of Sociabile, userID, user name and phone number will also be collected automatically to assist in handling the inquiry. This information will be used only for addressing the inquiry and will not be transmitted or reported to any third party or external entities. </p><p><u>18. Other Terms and Conditions</u></p><p><i>18.1 Legal Interpretation and Venue</i></p><p>The validity and interpretation of this Agreement and the legal relations of the parties to it shall be governed by the internal substantive laws of the State of New Mexico, U.S.A., exclusive of its conflict of law provisions. Unless the context otherwise requires, when used in this Agreement, the singular shall include the plural, the plural shall include the singular, and all nouns, pronouns and any variations thereof shall be deemed to refer to the masculine, feminine, or neuter, as the identity of the person or persons may require. The Parties agree that jurisdiction for any legal action brought related to this Agreement shall be limited to the courts of Bernalillo County (NM).</p><p><i>18.2 Dispute Resolution</i></p><p>In the event of any dispute under this Agreement, the parties shall attempt to resolve the dispute in a reasonable and equitable manner through negotiation within a reasonable time. In the event that a resolution cannot be achieved within a reasonable time, the parties agree to submit any disputes involving commercial issues to non-binding arbitration in a mutually agreeable time and location within the continental United States of America before the American Arbitration Association or an equivalent body selected by mutual agreement, in accordance with its Commercial Arbitration Rules. The parties agree that they shall be entitled to discovery in the same manner as though the dispute were within the jurisdiction of the Courts of the State of New Mexico, United States of America. Nothing in this provision shall preclude a party from obtaining temporary or permanent equitable or statutory relief from any court or other body as necessary to prevent irreparable damage to its interests or to prevent damages not adequately compensable in monetary terms.</p><p><i>18.3 Severability and Enforceability</i></p><p>Should any section, or portion thereof, of this Agreement be held invalid by reason of any law, statute or regulation existing now or in the future in any jurisdiction by any court of competent authority or by a legally enforceable directive of any governmental body, such section or portion thereof shall be validly reformed so as to reflect the intent of the parties as nearly as possible and, if unreformable, shall be deemed divisible and deleted with respect to such jurisdiction. In the event any provision of this Agreement is found to be unenforceable or invalid and unreformable, such provision shall be severable from this Agreement and shall not affect the enforceability or validity of any other provision contained in this Agreement.</p><p><i>18.4 Legal Fees</i></p><p>In the event of the commencement of suit to enforce any of the terms or conditions in this Agreement, each Party shall bear financial responsibility for their own attorney and legal fees regardless of which Party prevails.</p><p><i>18.5 Notices</i></p><p>All notices and other required communications under this Agreement shall be in writing, and shall be effective (a) when hand delivered, including delivery by messenger or courier service (or if delivery is refused, at the time of refusal), to the address set forth below, (b) when received or refused as evidenced by the postal receipt if sent by United States mail as Certified Mail, return receipt requested, with proper postage prepaid (or legal equivalent), addressed as set forth below or (c) when received digitally as evidenced by the delivered report of electronic mail: </p><p>Sociabile<br>6100 Cortaderia St. NE No. 3322<br>Albuquerque, New Mexico, 87111<br>Attn: Aaron French, President</p>";
                Library::output(true, '1', "No Error", $message);
            } elseif ($type == 2) {
                $message['privacy'] = '<h3>Sociabile Privacy Policy</h3>

<p>1. Privacy Notice</p>

<p>Sociabile LLC ("Sociabile," "we," "our") recognizes that privacy is important to our users, customers, and visitors. Our mission is to provide our users with a social network respecting privacy at all levels (individual, community, organizational). We provide this Privacy Policy to help our users make an informed decision concerning their continued use of Sociabile and the services we provide. If you do not agree to our practices or terms, please do not use Sociabile and our Services.</p>

<p>The use of Sociabile and its services in regard to any personal information provided on the Sociabile App remains subject to the terms of this Privacy Policy and our Terms of Service. This includes any personal information and non-personally identifying information provided on the Sociabile App by you. </p>


<p>2. What Does This Privacy Policy Cover?</p>

<p>This Privacy Policy is part of Sociabile\'s Terms of Service and explains how Sociabile will handle and safeguard your personal information, and features offered by us allowing you to manage your personal information. This Privacy Policy applies to: personal information you provide to us when using the Sociabile App and optional services made available within the Sociabile App. This Privacy Policy applies to all users, both within the United States and outside of the USociabile Privacy Policy</p>

<p>1. Privacy Notice</p>

<p>Sociabile LLC ("Sociabile," "we," "our") recognizes that privacy is important to our users, customers, and visitors. Our mission is to provide our users with a social network respecting privacy at all levels (individual, community, organizational). We provide this Privacy Policy to help our users make an informed decision concerning their continued use of Sociabile and the services we provide. If you do not agree to our practices or terms, please do not use Sociabile and our Services.</p>

<p>The use of Sociabile and its services in regard to any personal information provided on the Sociabile App remains subject to the terms of this Privacy Policy and our Terms of Service. This includes any personal information and non-personally identifying information provided on the Sociabile App by you. </p>


<p>2. What Does This Privacy Policy Cover?</p>

<p>This Privacy Policy is part of Sociabile\'s Terms of Service and explains how Sociabile will handle and safeguard your personal information, and features offered by us allowing you to manage your personal information. This Privacy Policy applies to: personal information you provide to us when using the Sociabile App and optional services made available within the Sociabile App. This Privacy Policy applies to all users, both within the United States and outside of the United States. This Privacy Policy does not apply to the practices of companies that Sociabile does not own or control.</p>


<p>3. Types of Information We Collect</p>

<p>Sociabile may obtain the following types of information from you or your mobile device, which may include information that can be used to identify you as specified below:</p>

<p>3.1 Account Information for Registration and Use: </p>

<p>When you register for Sociabile and create an account, Sociabile will ask for your phone number to register your account. This information is necessary because we will send text message via SMS to the provided phone number in an effort to detect and deter unauthorized or fraudulent use of or abuse of the Service. </p>

<p>If you choose to enable the "Find me by Phone Number" feature, your telephone number is used to recommend you as a friend to others who have your telephone number in their mobile device address book. This will allow others to send you a friend request from their recommended friends’ page. You may choose to enable or disable this feature at any time through the "Account Information" section under the "Settings" tab.</p>

<p>You may also provide personally identifiable information on the "My Profile" tab by posting "My Mind" messages, filling out your profile information, or uploading pictures. Only users whom you have connected with and granted permission can see the information you posted. All connections made through Sociabile must be organized into groups. You may grant permission to groups, allowing them to view content you posted, by going to the "Privacy Settings" page under the "Settings" tab.</p>

<p>3.2 User Provided Information</p>

<p>You provide certain Personally Identifiable Information, such as your mobile phone number, profile information, billing information and mobile device information to Sociabile when choosing to participate in various uses of Sociabile Services, such as registering as a user, updating your profile or purchasing emoticons. In order to provide the Sociabile Service, we will periodically access your address book or contact list on your mobile phone to locate the mobile phone numbers of other Sociabile users. In addition, you may be recommended to other Sociabile users who have your phone number in their address book. You may turn off this feature by going to Account Information under the "Settings" tab and disabling the "Find Me By Phone Number" option.</p>

<p>3.3 Log File Information</p>

<p>When you use the Sociabile App, our servers automatically record certain actions that are performed on the application. These server logs may include information such as userID, action performed, date/time action was performed, and a description of the action. This information is used to perform analytics to better understand how the app is being used. This will help us understand and enhance the user experience. This usage information may also be used for academic research. Any information published or provided to a third party will contain summated data statistics. We will not provide Personally Identifiable Information about our users to a third party.</p>


<p>4. Information Not Collected by Sociabile</p>

<p>Sociabile does not collect names, emails, addresses or other contact information from its users’ mobile address book or contact lists other than mobile phone numbers. Mobile numbers are used to identify other users of Sociabile and recommend them as friends in the "Friends" tab. You may choose to add the recommended friends, hide them, or do nothing.</p>

<p>Messages sent through Sociabile’s chatting service are not copied, stored, or archived by Sociabile in the normal course of business. Messages sent through our chatting service is considered "Private" messages that are only intended for the designated recipient. Information posted on your profile (i.e. profile information, My Mind messages, pictures and comments) are considered "Public" messages that are displayed on the application for those who have been given permission. Private messages reside on Sociabile’s hosting service until they are delivered to the intended recipient. If the message is not delivered within thirty (30) days it will be deemed undeliverable and deleted from the server. All successfully transmitted private messages are deleted from the server and will not be collected or saved by Sociabile. The only record of private messages sent through Sociabile reside on the sender’s and recipient’s mobile device. Public messages are stored on Sociabile’s hosting services to be displayed through the App to other users who have been granted permission. </p>

<p>5. How Sociabile Uses Information</p>

<p>When filling out your profile and uploading information to Sociable Services such as My Mind messages, comments and pictures, you may submit Personally Identifiable Information. This information is stored on Sociabile’s hosting service servers to be maintained and displayed on your profile. Your Personally Identifiable Information will not be sold or transmitted to any third party or outside entity. We do reserve the right to access usage statistics content submitted to Sociabile for research purposes. When research is conducted using Sociabile and user generated content, all Personally Identifiable Information will be removed. We will ensure the privacy and protection of all past, present and future Sociabile users. </p>

<p>6. When Sociabile Discloses Information</p>

<p>Only Sociabile users whom you have connected to and granted permission may view information that is posted publically on your account. For instance, you may post profile information, My Mind messages or pictures to your Sociabile page. These contents are viewable by other members who belong to a group that you have granted permission to. Your profile picture, user name, context indicator, and birthday (month and day only) are visible by all users whom you have established a connection with through the Sociabile app. For instance, the context indicator is set to "Available" by default but may be changed to any other predefined option or you may enter your own custom setting letting other users know your availability to chat. Only the month and day of your birthday is displayed on the Sociabile app for others to see. </p>

<p>We do not sell or share your Personally Identifiable Information with other third-party organizations for commercial or marketing use without your consent. We may share your Personally Identifiable Information with third party service providers to the extent that it is reasonably necessary to perform, improve or maintain the Sociabile App Service. We may share non-personally-identifiable information (such as usage information, log files, platform types, number of clicks, etc.) with interested third-parties in the pursuit of academic research or to assist them in understanding the usage patterns for certain content, services, and/or functionality of the Sociabile Service. We may collect and release Personally Identifiable Information and/or non-personally-identifiable information if required to do so by law, or in the good-faith belief that such action is necessary to comply with state and federal laws (such as U.S. Copyright Law), international law or to respond to a court order, subpoena, or search warrant or equivalent, or where in our reasonable belief, an individual’s physical safety may be at risk or threatened. Sociabile also reserves the right to disclose Personally Identifiable Information and/or non-personally-identifiable information that Sociabile believes, in good faith, is appropriate or necessary to enforce our Terms of Service, take precautions against liability, to investigate and defend itself against any third-party claims or allegations, to assist government enforcement agencies, to protect the security or integrity of the Sociabile app or our servers, and to protect the rights, property, or personal safety of Sociabile, our users or others.</p>

<p>7. Your Choices</p>

<p>While Sociabile provides an "About Me" section where you can post Personally Identifiable Information, you may also choose NOT to submit information to your profile. It is your choice whether you provide information on your profile or not. The only information required to use Sociabile is your phone number when registering. If you do not wish to validate your account by submitting your phone number then you may choose to decline using Sociabile’s Service. If you do not agree with our Terms of Service or Privacy Policy then do not use our Service and delete your account. By using Sociabile, you are agreeing to terms laid out in the Terms of Service and Privacy Policy.</p>

<p>8. Advertisements and Third Party Information</p>

<p>Sociabile is committed to providing an ad free environment and protecting your privacy from third party entities. Our goal is to provide a platform where you connect to only those whom you have chosen to be connected to.</p> 

<p>9. Information Security</p>

<p>Sociabile uses commercially reasonable physical, managerial, and technical safeguards to preserve the integrity and security of your personal information. However, we cannot ensure or warrant the security of any information you transmit to Sociabile through unsecured networks. The use of Sociabile and transmission of data through unsecured wifi or other unprotected networks is done so at your own risk and is not recommended. Once we receive your transmission of information, Sociabile makes commercially reasonable efforts to ensure the security of our systems. However, please note that this is not a guarantee that such information may not be accessed, disclosed, altered, or destroyed by breach of any of our physical, technical, or managerial safeguards. If Sociabile discovers a security systems breach, then we may attempt to notify you electronically so that you can take appropriate protective steps. Sociabile may post a notice on our website or through the Sociabile app if a security breach occurs.</p>

<p>10. International Users of Sociabile</p>

<p>The Sociabile Service is hosted in the United States and are intended for and directed to users in the United States. If you are a user accessing the Sociabile Services from the European Union, Asia, or any other region with laws or regulations governing personal data collection, use, and disclosure, that differ from United States laws, please be advised that through your continued use of the Sociabile Service, which are governed by New Mexico law, this Privacy Policy, and our Terms of Service, you are transferring your personal information to the United States and you expressly consent to that transfer and consent to be governed by New Mexico law for these purposes.</p>

<p>11. In the Event of Merger, Sale, or Bankruptcy</p>

<p>In the event that Sociabile is acquired by or merged with a third party entity, we reserve the right to transfer or assign the information we have collected from our users as part of such merger, acquisition, sale, or other change of control. In the (hopefully) unlikely event of our bankruptcy, insolvency, reorganization, receivership, or assignment for the benefit of creditors, or the application of laws or equitable principles affecting creditors\' rights generally, we may not be able to control how your personal information is treated, transferred, or used.</p>

<p>12. Changes and updates to this Privacy Notice</p>

<p>This Privacy Policy may be revised periodically and this will be reflected by the "effective date" below. Please revisit this page to stay aware of any changes. Your continued use of Sociabile Services constitutes your agreement to this Privacy Policy and any amendments.</p>

<p>Date Last Modified: April 14th, 2015nited States. This Privacy Policy does not apply to the practices of companies that Sociabile does not own or control.</p>


<p>3. Types of Information We Collect</p>

<p>Sociabile may obtain the following types of information from you or your mobile device, which may include information that can be used to identify you as specified below:</p>

<p>3.1 Account Information for Registration and Use: </p>

<p>When you register for Sociabile and create an account, Sociabile will ask for your phone number to register your account. This information is necessary because we will send text message via SMS to the provided phone number in an effort to detect and deter unauthorized or fraudulent use of or abuse of the Service. </p>

<p>If you choose to enable the "Find me by Phone Number" feature, your telephone number is used to recommend you as a friend to others who have your telephone number in their mobile device address book. This will allow others to send you a friend request from their recommended friends’ page. You may choose to enable or disable this feature at any time through the "Account Information" section under the "Settings" tab.</p>

<p>You may also provide personally identifiable information on the "My Profile" tab by posting "My Mind" messages, filling out your profile information, or uploading pictures. Only users whom you have connected with and granted permission can see the information you posted. All connections made through Sociabile must be organized into groups. You may grant permission to groups, allowing them to view content you posted, by going to the "Privacy Settings" page under the "Settings" tab.</p>

<p>3.2 User Provided Information</p>

<p>You provide certain Personally Identifiable Information, such as your mobile phone number, profile information, billing information and mobile device information to Sociabile when choosing to participate in various uses of Sociabile Services, such as registering as a user, updating your profile or purchasing emoticons. In order to provide the Sociabile Service, we will periodically access your address book or contact list on your mobile phone to locate the mobile phone numbers of other Sociabile users. In addition, you may be recommended to other Sociabile users who have your phone number in their address book. You may turn off this feature by going to Account Information under the "Settings" tab and disabling the "Find Me By Phone Number" option.</p>

<p>3.3 Log File Information</p>

<p>When you use the Sociabile App, our servers automatically record certain actions that are performed on the application. These server logs may include information such as userID, action performed, date/time action was performed, and a description of the action. This information is used to perform analytics to better understand how the app is being used. This will help us understand and enhance the user experience. This usage information may also be used for academic research. Any information published or provided to a third party will contain summated data statistics. We will not provide Personally Identifiable Information about our users to a third party.</p>


<p>4. Information Not Collected by Sociabile</p>

<p>Sociabile does not collect names, emails, addresses or other contact information from its users’ mobile address book or contact lists other than mobile phone numbers. Mobile numbers are used to identify other users of Sociabile and recommend them as friends in the "Friends" tab. You may choose to add the recommended friends, hide them, or do nothing.</p>

<p>Messages sent through Sociabile’s chatting service are not copied, stored, or archived by Sociabile in the normal course of business. Messages sent through our chatting service is considered "Private" messages that are only intended for the designated recipient. Information posted on your profile (i.e. profile information, My Mind messages, pictures and comments) are considered "Public" messages that are displayed on the application for those who have been given permission. Private messages reside on Sociabile’s hosting service until they are delivered to the intended recipient. If the message is not delivered within thirty (30) days it will be deemed undeliverable and deleted from the server. All successfully transmitted private messages are deleted from the server and will not be collected or saved by Sociabile. The only record of private messages sent through Sociabile reside on the sender’s and recipient’s mobile device. Public messages are stored on Sociabile’s hosting services to be displayed through the App to other users who have been granted permission. </p>

<p>5. How Sociabile Uses Information</p>

<p>When filling out your profile and uploading information to Sociable Services such as My Mind messages, comments and pictures, you may submit Personally Identifiable Information. This information is stored on Sociabile’s hosting service servers to be maintained and displayed on your profile. Your Personally Identifiable Information will not be sold or transmitted to any third party or outside entity. We do reserve the right to access usage statistics content submitted to Sociabile for research purposes. When research is conducted using Sociabile and user generated content, all Personally Identifiable Information will be removed. We will ensure the privacy and protection of all past, present and future Sociabile users. </p>

<p>6. When Sociabile Discloses Information</p>

<p>Only Sociabile users whom you have connected to and granted permission may view information that is posted publically on your account. For instance, you may post profile information, My Mind messages or pictures to your Sociabile page. These contents are viewable by other members who belong to a group that you have granted permission to. Your profile picture, user name, context indicator, and birthday (month and day only) are visible by all users whom you have established a connection with through the Sociabile app. For instance, the context indicator is set to "Available" by default but may be changed to any other predefined option or you may enter your own custom setting letting other users know your availability to chat. Only the month and day of your birthday is displayed on the Sociabile app for others to see. </p>

<p>We do not sell or share your Personally Identifiable Information with other third-party organizations for commercial or marketing use without your consent. We may share your Personally Identifiable Information with third party service providers to the extent that it is reasonably necessary to perform, improve or maintain the Sociabile App Service. We may share non-personally-identifiable information (such as usage information, log files, platform types, number of clicks, etc.) with interested third-parties in the pursuit of academic research or to assist them in understanding the usage patterns for certain content, services, and/or functionality of the Sociabile Service. We may collect and release Personally Identifiable Information and/or non-personally-identifiable information if required to do so by law, or in the good-faith belief that such action is necessary to comply with state and federal laws (such as U.S. Copyright Law), international law or to respond to a court order, subpoena, or search warrant or equivalent, or where in our reasonable belief, an individual’s physical safety may be at risk or threatened. Sociabile also reserves the right to disclose Personally Identifiable Information and/or non-personally-identifiable information that Sociabile believes, in good faith, is appropriate or necessary to enforce our Terms of Service, take precautions against liability, to investigate and defend itself against any third-party claims or allegations, to assist government enforcement agencies, to protect the security or integrity of the Sociabile app or our servers, and to protect the rights, property, or personal safety of Sociabile, our users or others.</p>

<p>7. Your Choices</p>

<p>While Sociabile provides an "About Me" section where you can post Personally Identifiable Information, you may also choose NOT to submit information to your profile. It is your choice whether you provide information on your profile or not. The only information required to use Sociabile is your phone number when registering. If you do not wish to validate your account by submitting your phone number then you may choose to decline using Sociabile’s Service. If you do not agree with our Terms of Service or Privacy Policy then do not use our Service and delete your account. By using Sociabile, you are agreeing to terms laid out in the Terms of Service and Privacy Policy.</p>

<p>8. Advertisements and Third Party Information</p>

<p>Sociabile is committed to providing an ad free environment and protecting your privacy from third party entities. Our goal is to provide a platform where you connect to only those whom you have chosen to be connected to. </p>

<p>9. Information Security</p>

<p>Sociabile uses commercially reasonable physical, managerial, and technical safeguards to preserve the integrity and security of your personal information. However, we cannot ensure or warrant the security of any information you transmit to Sociabile through unsecured networks. The use of Sociabile and transmission of data through unsecured wifi or other unprotected networks is done so at your own risk and is not recommended. Once we receive your transmission of information, Sociabile makes commercially reasonable efforts to ensure the security of our systems. However, please note that this is not a guarantee that such information may not be accessed, disclosed, altered, or destroyed by breach of any of our physical, technical, or managerial safeguards. If Sociabile discovers a security systems breach, then we may attempt to notify you electronically so that you can take appropriate protective steps. Sociabile may post a notice on our website or through the Sociabile app if a security breach occurs.</p>

<p>10. International Users of Sociabile</p>

<p>The Sociabile Service is hosted in the United States and are intended for and directed to users in the United States. If you are a user accessing the Sociabile Services from the European Union, Asia, or any other region with laws or regulations governing personal data collection, use, and disclosure, that differ from United States laws, please be advised that through your continued use of the Sociabile Service, which are governed by New Mexico law, this Privacy Policy, and our Terms of Service, you are transferring your personal information to the United States and you expressly consent to that transfer and consent to be governed by New Mexico law for these purposes.</p>

<p>11. In the Event of Merger, Sale, or Bankruptcy</p>

<p>In the event that Sociabile is acquired by or merged with a third party entity, we reserve the right to transfer or assign the information we have collected from our users as part of such merger, acquisition, sale, or other change of control. In the (hopefully) unlikely event of our bankruptcy, insolvency, reorganization, receivership, or assignment for the benefit of creditors, or the application of laws or equitable principles affecting creditors\' rights generally, we may not be able to control how your personal information is treated, transferred, or used.</p>

<p>12. Changes and updates to this Privacy Notice</p>

<p>This Privacy Policy may be revised periodically and this will be reflected by the "effective date" below. Please revisit this page to stay aware of any changes. Your continued use of Sociabile Services constitutes your agreement to this Privacy Policy and any amendments.</p>

<p>Date Last Modified: April 14th, 2015</p>
';
                Library::output(true, '1', "No Error", $message);
            } else {
                Library::output(false, '0', "Wrong Type", null);
            }
        } catch(Exception $e) {
            Library::logging('error',"API : aboutSoicabile, error_msg : ".$e." ".": user_id : ".$header_data['id']."type :".$type);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    
    /**
     * Method for set privacy settings
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function setPrivacySettingsAction($header_data,$post_data)
    {
        if( (!isset($post_data['group_ids']) && $header_data['os'] == 1) || !isset($post_data['type'])) {
            Library::logging('alert',"API : setPrivacySettings : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 1) {
                    $group_ids = json_decode($post_data['group_ids']);
                } else {
                    if( empty($post_data['group_ids']) ){
                        $group_ids = array();
                    }else{
                        $group_ids = $post_data['group_ids'];
                    }
                }
                $user = Users::findById($header_data['id']);
                if($post_data['type'] == 1) {
                    $user->my_mind_groups = $group_ids;
                } elseif ($post_data['type'] == 2) {
                    $user->about_me_groups = $group_ids;
                } elseif ($post_data['type'] == 3) {
                    $user->my_pictures_groups = $group_ids;
                } else {
                    Library::output(false, '0', "Wrong Type", null);
                }
                if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                        Library::logging('error',"API : setPrivacySettings, error_msg : ".$errors." : user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                } else {
                    Library::output(true, '1', PRIVACY_SETTINGS, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : setPrivacySettings, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    
    /**
     * Method for get privacy settings
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getPrivacySettingsAction($header_data,$type)
    {
        try {
            $result = array();
            $user = Users::findById($header_data['id']);
            if($type == 1) {
                $result['my_mind'] = $user->my_mind_groups;
                Library::output(true, '1', "No Error", $result);
            } elseif ($type == 2) {
                $result['my_mind'] = $user->about_me_groups;
                Library::output(true, '1', "No Error", $result);
            } elseif($type == 3) {
               $result['my_mind'] = $user->my_pictures_groups;
                Library::output(true, '1', "No Error", $result);
            } else {
                Library::output(false, '0', "Wrong Type", null);
            }
        } catch(Exception $e) {
            Library::logging('error',"API : getPrivacySettings, error_msg : ".$e." ".": user_id : ".$header_data['id']."type :".$type);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    
    
    /**
     * Method for share photos
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function sharePhotosAction($header_data,$post_data)
    {
        if( (empty($post_data['group_id']) && empty($post_data['friends_id'])) || empty($post_data['image_name'])) {
            Library::logging('alert',"API : sharePhotos : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post_data['image_name']    = str_replace( FORM_ACTION, "", $post_data['image_name'] );
                $sharedWith                 = array();
                $user                       = Users::findById( $header_data['id'] );
                if( !empty($post_data['group_id']) ){
                    if($header_data['os'] == 1) {
                        $group_ids =  json_decode($post_data['group_id']);
                    } else {
                        $group_ids =  $post_data['group_id'];
                    }
                    if( !empty($user->running_groups) ){
                        foreach ($user->running_groups as $detail ) {
                            if( count(array_intersect($detail['group_id'], $group_ids)) > 0 ) {
                                $sharedWith[$detail["user_id"]] = $detail["user_id"];
                            }
                        }
                    }
                }
                if( !empty($post_data['friends_id']) ){
                    if($header_data['os'] == 1) {
                        $friends_id =  json_decode($post_data['friends_id']);
                    } else {
                        $friends_id =  $post_data['friends_id'];
                    }
                    if( !empty($user->running_groups) ){
                        foreach ($user->running_groups as $detail ) {
                            if( in_array($detail['user_id'], $friends_id) ) {
                                $sharedWith[$detail["user_id"]] = $detail["user_id"];
                            }
                        }
                    }
                }
                $post                   = new Posts();
                $post->user_id          = $header_data['id'];
                $post->text             = $post_data['image_name'];
                $post->total_comments   = 0;
                $post->likes            = 0;
                $post->dislikes         = 0;
                $post->date             = time();
                $post->shared_with      = array_values($sharedWith);
                $post->viewed           = 0;
                $post->type             = 3;    // type| 1 for text posts, 2 for images, 3 for shared images
                if ($post->save() == false) {
                    foreach ($post->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : sharePhotos : ".$errors." user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                }
                Library::output(true, '1', SHARE_IMAGE, null);
            } catch(Exception $e) {
                Library::logging('error',"API : sharePhotos, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    /**
     * Method for get shared photos people 
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Saurabh Kumar
     * @return json
     */
    
    public function getPostSharedWithAction($header_data,$post_data)
    {
        if( empty($post_data['post_id']) ) {
            Library::logging('alert',"API : getPostSharedWith : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   =   Posts::findById($post_data['post_id']);
                $result =   isset($post->shared_with) ? $post->shared_with : array(); 
                Library::output(true, '1', "no error", $result);
            } catch(Exception $e) {
                Library::logging('error',"API : sharePhotos, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    public function viewSharedImageAction( $header_data, $post_data ){
        if( !isset($post_data['post_id'])) {
            Library::logging('alert',"API : viewSharedImage : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                if($header_data['os'] == 1) {
                    $post_data['post_id'] =  json_decode($post_data['post_id']);
                }
                if( !is_array($post_data['post_id']) ) {
                    Library::logging('alert',"API : viewSharedImage : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_INPUT, null);
                }                
                $ids    = "[ ";
                foreach( $post_data['post_id'] AS $postId ){
                    $ids    .= 'ObjectId("'.$postId.'"), ';
                }
                $ids    = substr($ids, 0, -1)." ]"; 
                $db     = Library::getMongo();
                $query  = 'return db.posts.update( {"_id" :{$in:'.$ids.'} }, { $set:{viewed:1} }, { multi:true } )';
                $update = $db->execute($query);
                if($update['ok'] == 0) {
                    Library::logging('error',"API : viewSharedImage mongodb error: ".$update['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "No error", null);
                
            } catch(Exception $e) {
                Library::logging('error',"API : viewSharedImage, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
        
    }
    
    
    public function deleteSharedImageAction( $header_data, $post_data ){
        if( !isset($post_data['post_id'])) {
            Library::logging('alert',"API : deleteSharedImage : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $post   = Posts::findById( $post_data['post_id'] );
                if($post){
                    if( ($key=array_search($header_data['id'], $post->shared_with )) !== false ){
                        unset($post->shared_with[$key]);
                        $post->shared_with  = array_values( $post->shared_with );
                        if ($post->save() == false) {
                            foreach ($post->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            Library::logging('error',"API : deleteSharedImage : ".$errors." user_id : ".$header_data['id']);
                            Library::output(false, '0', $errors, null);
                        }
                    }
                    Library::output(true, '1', "Shared image deleted successfully", null);
                }else{
                    Library::logging('error',"API : deleteSharedImage : Invalid Post Id : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : deleteSharedImage, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
        
    }
    /**
     * Method for get other friends profile info
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getFriendsInfoAction($header_data,$user_id)
    {
        if(empty($user_id)) {
            Library::logging('alert',"API : getFriendsInfo : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $user = Users::findById($user_id);
                if(  !empty($user) && $user->is_active && ($user->is_deleted == 0) && $user->running_groups) {

                    // loop for finding particular groups 
                    foreach ($user->running_groups as $group) {
                        if($group['user_id'] == $header_data['id']) {
                            $groups = $group['group_id'];
                            break;
                        } 
                    }
                    if(is_array($groups)) {
                        
                        // loop for checking whether user can see my mind section
                        foreach($groups as $friends_group) {
                            foreach($user->my_mind_groups as $my_minds_groups) {
                                if($friends_group == $my_minds_groups) {
                                    $my_mind = 1;
                                    break;
                                } else {
                                    $my_mind = 0;
                                }
                            }
                            if($my_mind == 1) {
                                break;
                            }
                        }
                        
                        // loop for checking whether user can see about me section
                        foreach($groups as $friends_group) {
                            foreach($user->about_me_groups as $about_me_groups) {
                                if($friends_group == $about_me_groups) {
                                    $about_me = 1;
                                    break;
                                } else {
                                    $about_me = 0;
                                }
                            }
                            if($about_me == 1) {
                                break;
                            }
                        }
                        
                        
                        // loop for checking whether user can see my pictures section
                        foreach($groups as $friends_group) {
                            foreach($user->my_pictures_groups as $my_pictures_groups) {
                                if($friends_group == $my_pictures_groups) {
                                    $my_pictures = 1;
                                    break;
                                } else {
                                    $my_pictures = 0;
                                }
                            }
                            if($my_pictures == 1) {
                                break;
                            }
                        }

                        $i          = 0;
                        $user_post  = array();
                        if($my_mind == 1) {
                            $posts = Posts::find(array(array("user_id" => $user_id, "type"=>1)));
                            if(is_array($posts)) {
                                foreach($posts as $post) {
                                    if( !empty($post->shared_with) ){
                                        if( !in_array($header_data['id'], $post->shared_with) ){
                                            continue;
                                        }
                                    }
                                    $isLiked    = false;
                                    $isDisliked = false;
                                    if( !empty($post->liked_by) && in_array( $header_data['id'], $post->liked_by) ){
                                        $isLiked    = true;
                                    }
                                    if( !empty($post->disliked_by) && in_array( $header_data['id'], $post->disliked_by) ){
                                        $isDisliked = true;
                                    }
                                    $user_post[$i]['post_id']               = (string)$post->_id;
                                    $user_post[$i]['user_name']             = $post->username;
                                    $user_post[$i]['post_text']             = $post->text;
                                    $user_post[$i]['post_comment_count']    = $post->total_comments;
                                    $user_post[$i]['post_like_count']       = $post->likes;
                                    $user_post[$i]['post_dislike_count']    = $post->dislikes;
                                    $user_post[$i]['is_liked']              = $isLiked;
                                    $user_post[$i]['is_disliked']           = $isDisliked;
                                    $user_post[$i]['post_timestamp']        = $post->date;
                                    $i++;
                                }
                            }
                        }
                        usort($user_post, function($postA, $postB){
                            if ($postA["date"] == $postB["date"]) {
                                return 0;
                            }
                            return ($postA["date"] < $postB["date"]) ? 1 : -1;
                        });       
                        
                        if($about_me == 1) {
                            $about_me_info['gender'] = isset($user->gender) ? $user->gender : '';
                            $about_me_info['hobbies'] = isset($user->hobbies) ? $user->hobbies : '';
                            $about_me_info['description'] = isset($user->about_me) ? $user->about_me : '';
                        } else {
                            $about_me_info['gender'] = '';
                            $about_me_info['hobbies'] = '';
                            $about_me_info['description'] = '';
                        }

                        $my_pictures_info   = array();
                        if($my_pictures == 1) {
                            $posts = Posts::find(array(array("user_id" => $user_id, "type"=>2)));
                            if(is_array($posts)) {
                                foreach($posts as $post) {
                                    if( !empty($post->shared_with) ){
                                        if( !in_array($header_data['id'], $post->shared_with) ){
                                            continue;
                                        }
                                    }
                                    $postId = (string)$post->_id;
                                    $isLiked    = false;
                                    $isDisliked = false;
                                    if( !empty($post->liked_by) && in_array( $header_data['id'], $post->liked_by) ){
                                        $isLiked    = true;
                                    }
                                    if( !empty($post->disliked_by) && in_array( $header_data['id'], $post->disliked_by) ){
                                        $isDisliked = true;
                                    }
                                    if( is_array($post->text) ){
                                        continue;
                                    }
                                    $my_pictures_info[$postId]['post_id']           = $postId;
                                    $my_pictures_info[$postId]['text']              = FORM_ACTION.$post->text;
                                    $my_pictures_info[$postId]['user_name']         = $user->username;
                                    $my_pictures_info[$postId]['total_comments']    = $post->total_comments;
                                    $my_pictures_info[$postId]['likes']             = $post->likes;
                                    $my_pictures_info[$postId]['dislikes']          = $post->dislikes;
                                    $my_pictures_info[$postId]['date']              = $post->date;
                                    $my_pictures_info[$postId]['is_liked']          = $isLiked;
                                    $my_pictures_info[$postId]['is_disliked']       = $isDisliked;
                                    $my_pictures_info[$postId]['multiple']          = 0;
                                }
                            }
                        }
                        usort($my_pictures_info, function($postA, $postB){
                            if ($postA["date"] == $postB["date"]) {
                                return 0;
                            }
                            return ($postA["date"] < $postB["date"]) ? 1 : -1;
                        });       
                        
                        $profile['image_url'] = FORM_ACTION;
                        $profile['mobile_no'] = $user->mobile_no;
                        $profile['username'] = $user->username;
                        $profile['context_indicator'] = $user->context_indicator;
                        $profile['birthday'] = isset($user->birthday) ? $user->birthday : '';
                        $profile['profile_pic'] = isset($user->profile_image) ? FORM_ACTION.$user->profile_image : 'http://www.gettyimages.in/CMS/StaticContent/1391099126452_hero1.jpg';

                        $result['profile']      = $profile;
                        $result['my_mind']      = $user_post;
                        $result['about_me']     = isset($about_me_info) ? $about_me_info : '';
                        $result['my_pictures']  = array_values($my_pictures_info);

                        Library::output(true, '1', "No Error", $result);
                    } else {
                        Library::output(false, '0', "Wrong User Id", null);
                    }

                } else {
                    Library::output(false, '0', INVALID_ID, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : getFriegndsInfo, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    /**
     * Method for get Images uploaded by user and shared with him by friends
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function getImagesAction($header_data,$type)
    {
        try {
            $db         = Library::getMongo();
            $userResult = $db->execute('return db.users.find({"_id":ObjectId("'.$header_data['id'].'")}).toArray()');
            if($userResult['ok'] == 0) {
                Library::logging('error',"API : getImages (get user info) , mongodb error: ".$userResult['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }    
            $user   = $userResult['retval'][0];
            
            if( $type == 1 ) { // type 1 for uploaded images
                $posts  = $db->execute('return db.posts.find( {"user_id":"'.$header_data['id'].'", "type":2} ).toArray()');
                if($posts['ok'] == 0) {
                    Library::logging('error',"API : getImages (get user info) , mongodb error: ".$posts['errmsg']." ".": user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                $result     = array();
                foreach( $posts['retval'] As $postDetail ){
                    $isLiked    = false;
                    $isDisliked = false;
                    if( !empty($postDetail["liked_by"]) && in_array( $header_data['id'], $postDetail["liked_by"]) ){
                        $isLiked    = true;
                    }
                    if( !empty($postDetail["disliked_by"]) && in_array( $header_data['id'], $postDetail["disliked_by"]) ){
                        $isDisliked = true;
                    }
                    $postDetail["text"] = (!is_array($postDetail["text"])) ? FORM_ACTION.$postDetail["text"] : $postDetail["text"];
                    if( is_array($postDetail["text"]) ){
                        continue;
                    }
                    $username   = isset($user["username"]) ? $user["username"] : 'user';
                    $postId = (string)$postDetail["_id"];
                    $result[$postId]["post_id"]             = (string)$postDetail["_id"];
                    $result[$postId]["user_name"]           = $username;
                    $result[$postId]["text"]                = $postDetail["text"];
                    $result[$postId]["date"]                = $postDetail["date"];
                    $result[$postId]["likes"]               = $postDetail["likes"];
                    $result[$postId]["dislikes"]            = $postDetail["dislikes"];
                    $result[$postId]["total_comments"]      = $postDetail["total_comments"];
                    $result[$postId]["is_liked"]            = $isLiked;
                    $result[$postId]["is_disliked"]         = $isDisliked;
                    $result[$postId]["multiple"]            = 0;
                }
            }
            elseif( $type == 2 ) { // type 2 for share images
                
                $result     = array();
                        $posts  = $db->execute('return db.posts.find({"type":3, "shared_with":"'.$header_data['id'].'"}).toArray()');
                        if($posts['ok'] == 0) {
                            Library::logging('error',"API : getImages (get user info) , mongodb error: ".$posts['errmsg']." ".": user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        }
                        foreach( $posts['retval'] As $postDetail ){ 
                            $isLiked    = false;
                            $isDisliked = false;
                            if( !empty($postDetail["liked_by"]) && in_array( $header_data['id'], $postDetail["liked_by"]) ){
                                $isLiked    = true;
                            }
                            if( !empty($postDetail["disliked_by"]) && in_array( $header_data['id'], $postDetail["disliked_by"]) ){
                                $isDisliked = true;
                            }
                            if( is_array($postDetail["text"]) ){
                                continue;
                            }
                            $postDetail["text"] = FORM_ACTION.$postDetail["text"];
                            
                            $friendsResult  = $db->execute('return db.users.find({"_id":ObjectId("'.$postDetail['user_id'].'")}, {username:1}).toArray()');
                            if($friendsResult['ok'] == 0) {
                                Library::logging('error',"API : getImages (get friends info) , mongodb error: ".$friendsResult['errmsg']." ".": user_id : ".$header_data['id']);
                                Library::output(false, '0', ERROR_REQUEST, null);
                            } 
                            $friends_info   = $friendsResult['retval'][0];
                            
                            $username   = isset($friends_info["username"]) ? $friends_info["username"] : 'user';
                            $postId     = (string)$postDetail["_id"];
                            $result[$postId]["post_id"]             = (string)$postDetail["_id"];
                            $result[$postId]["user_name"]           = $username;
                            $result[$postId]["text"]                = $postDetail["text"];
                            $result[$postId]["date"]                = $postDetail["date"];
                            $result[$postId]["likes"]               = $postDetail["likes"];
                            $result[$postId]["dislikes"]            = $postDetail["dislikes"];
                            $result[$postId]["total_comments"]      = $postDetail["total_comments"];
                            $result[$postId]["is_liked"]            = $isLiked;
                            $result[$postId]["is_disliked"]         = $isDisliked;
                            $result[$postId]["multiple"]            = 0;
                            $result[$postId]["viewed"]              = $postDetail["viewed"];
                            
                        }
                        /*
                 if(isset($user['running_groups'])) {
                     foreach ($user['running_groups'] as $groups) {
                        $friendsResult  = $db->execute('return db.users.find({"_id":ObjectId("'.$groups['user_id'].'")}).toArray()');
                        if($friendsResult['ok'] == 0) {
                            Library::logging('error',"API : getImages (get friends info) , mongodb error: ".$friendsResult['errmsg']." ".": user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        } 
                        $friends_info   = $friendsResult['retval'][0];
                        if( empty($friends_info['running_groups']) ){
                            continue;
                        }
                        
                        // $friendsGroup will contain the groups in which friend has put the user
                        $friendsGroup   = array();
                        foreach($friends_info['running_groups'] as $grps) {
                            if( $grps["user_id"] == $header_data['id'] ){
                                $friendsGroup   = $grps["group_id"];
                                break;
                            }
                        }
                        
                        $posts  = $db->execute('return db.posts.find({"user_id":"'.(string)$friends_info['_id'].'", "type":3}).toArray()');
                        if($posts['ok'] == 0) {
                            Library::logging('error',"API : getImages (get user info) , mongodb error: ".$posts['errmsg']." ".": user_id : ".$header_data['id']);
                            Library::output(false, '0', ERROR_REQUEST, null);
                        }
                        foreach( $posts['retval'] As $postDetail ){ 
                            if( count(array_intersect($friendsGroup, $postDetail["group_id"])) ){
                                $isLiked    = false;
                                $isDisliked = false;
                                if( !empty($postDetail["liked_by"]) && in_array( $header_data['id'], $postDetail["liked_by"]) ){
                                    $isLiked    = true;
                                }
                                if( !empty($postDetail["disliked_by"]) && in_array( $header_data['id'], $postDetail["disliked_by"]) ){
                                    $isDisliked = true;
                                }
                                $postDetail["text"] = (!is_array($postDetail["text"])) ? FORM_ACTION.$postDetail["text"] : $postDetail["text"];
                                if( is_array($postDetail["text"]) ){
                                    continue;
                                }
                                $username   = isset($friends_info["username"]) ? $friends_info["username"] : '';
                                $postId     = (string)$postDetail["_id"];
                                $result[$postId]["post_id"]             = (string)$postDetail["_id"];
                                $result[$postId]["user_name"]           = $username;
                                $result[$postId]["text"]                = $postDetail["text"];
                                $result[$postId]["date"]                = $postDetail["date"];
                                $result[$postId]["likes"]               = $postDetail["likes"];
                                $result[$postId]["dislikes"]            = $postDetail["dislikes"];
                                $result[$postId]["total_comments"]      = $postDetail["total_comments"];
                                $result[$postId]["is_liked"]            = $isLiked;
                                $result[$postId]["is_disliked"]         = $isDisliked;
                                $result[$postId]["multiple"]            = 0;
                            }
                        }
                     }
                 }
                         * 
                         */
             } else {
                 Library::output(false, '0', WRONG_TYPE, null);
             }
             
            Library::output(true, '1', "No Error", array_values($result) );
        } catch(Exception $e) {
            Library::logging('error',"API : getImages, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
        
    }
    
    
    /**
     * Method for user login
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function userLoginAction($header_data,$post_data)
    {
        if(!isset($post_data['password']) || !isset($post_data['unique_id'])) {
            Library::logging('alert',"API : userLogin : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            $post_data['unique_id'] = strtolower($post_data['unique_id']);
            try {
                $security = new \Phalcon\Security();
                $user = Users::findById($header_data['id']);
                if ($security->checkHash($post_data['password'], $user->password) && $user->unique_id == $post_data['unique_id']) {
                    Library::output(true, '1', USER_LOGIN, null);
                } else {
                    Library::output(false, '0', INVALID_LOGIN, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : userLogin, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            
        }
    }
    
    public function uploadChatImageAction($header_data,$post_data)
    {
        if( empty($_FILES["images"]['name']) ) {
            Library::logging('alert',"API : uploadChatImage : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                foreach( $_FILES["images"]['name'] As $key=>$value ){
                    $post_data['images'][]  = array( "name"=>$value, "tmp_name"=>$_FILES["images"]["tmp_name"][$key]) ;
                }
                $result = array();
                $amazon = new AmazonsController();
                $count  = 0;
                foreach( $post_data['images'] As $image ){
                    $uploadFile = rand().$image["name"];
                    $amazonSign = $amazon->createsignatureAction($header_data,10);
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $ext        = explode(".", $uploadFile);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       => "chat/".$uploadFile,
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => file_get_contents($image["tmp_name"])
                    );
                    $ch = curl_init();
                    $options = array(
                        CURLOPT_URL         => $url,
                        CURLOPT_POST        => 1,
                        CURLOPT_HTTPHEADER  => $headers,
                        CURLOPT_POSTFIELDS  => $postfields,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true
                    ); // cURL options
                    curl_setopt_array($ch, $options);
                    $imageName      = curl_exec($ch);
                    curl_close($ch);
                    
//                    $postfields["key"] = "thumbnail/".$uploadFile;
//                    $postfields["file"] = $amazon->createThumbnail(FORM_ACTION.$imageName);
//                    $ch = curl_init();
//                    $options[CURLOPT_POSTFIELDS]    = $postfields;
//                    curl_setopt_array($ch, $options);
//                    $thumbnailName  = curl_exec($ch);
//                    curl_close($ch);
                    $result[]   = array( "image"=>FORM_ACTION.$imageName/*, "thumbnail"=>FORM_ACTION.$thumbnailName*/ );
                }
                Library::output(true, '1', POST_SAVED, $result);
            } catch (Exception $e) {
                Library::logging('error',"API : uploadChatImage : ".$e." ".": user_id : ".$header_data["id"]);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    public function uploadMultipleImagesAction($header_data,$post_data)
    {
        if( empty($_FILES["images"]['name']) ) {
            Library::logging('alert',"API : uploadMultipleImages : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                foreach( $_FILES["images"]['name'] As $key=>$value ){
                    $post_data['images'][]  = array( "name"=>$value, "tmp_name"=>$_FILES["images"]["tmp_name"][$key]) ;
                }
                $post                   = new Posts();
                $post->user_id          = $header_data["id"];
                $post->text             = array();
                $post->total_comments   = 0;
                $post->likes            = 0;
                $post->dislikes         = 0;
                $post->date             = time();
                $post->type             = 2;    // type| 1 for text posts, 2 for images
                if ($post->save() == false) {
                    foreach ($post->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : uploadMultipleImages : ".$errors." user_id : ".$header_data["id"]);
                    Library::output(false, '0', $errors, null);
                }
                $db     = Library::getMongo();
                $result = array();
                foreach( $post_data['images'] As $image ){
                   // $image_name = $image["file"];
                    $uploadFile = rand().$image["name"];
                    $amazon     = new AmazonsController();
                    $amazonSign = $amazon->createsignatureAction($header_data,10);
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $ext        = explode(".", $uploadFile);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       => "uploaded/".$uploadFile,
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => file_get_contents($image["tmp_name"])
                    );
                    $ch = curl_init();
                    $options = array(
                        CURLOPT_URL         => $url,
                        //CURLOPT_HEADER      => true,
                        CURLOPT_POST        => 1,
                        CURLOPT_HTTPHEADER  => $headers,
                        CURLOPT_POSTFIELDS  => $postfields,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true
                    ); // cURL options
                    curl_setopt_array($ch, $options);
                    $imageName      = curl_exec($ch);
                    curl_close($ch);
                    $createdAt  = time();
                    $update = $db->execute('db.posts.insert( { "user_id" : "'.$header_data["id"].'", text: "'.$imageName.'", total_comments:0, likes:0, dislikes:0, date: "'.$createdAt.'", type:2 } )');
                    if( $update['ok'] == 0 ){
                        Library::logging('error',"API : uploadMultipleImages, mongodb error: ".$update['errmsg']." : user_id : ".$header_data["id"]);
                        Library::output(false, '0', "Unable to update images", null);
                    }
                    $res    = $db->execute('return db.posts.find( { "user_id" : "'.$header_data["id"].'", text: "'.$imageName.'", total_comments:0, likes:0, dislikes:0, date: "'.$createdAt.'", type:2 } ).toArray()');
                    if( $res['ok'] == 0 ){
                        Library::logging('error',"API : uploadMultipleImages, mongodb error: ".$res['errmsg']." : user_id : ".$header_data["id"]);
                        Library::output(false, '0', "Unable to update images", null);
                    }
                    $postId         = (string)$res["retval"][0]["_id"];
                    $post->text[]   = $postId;
                    $postArr        = array();
                    $postArr["post_id"]         = $postId;
                    $postArr["user_id"]         = $header_data["id"];
                    $postArr["text"]            = FORM_ACTION.$imageName;
                    $postArr["date"]            = $createdAt;
                    $postArr["likes"]           = 0;
                    $postArr["dislikes"]        = 0;
                    $postArr["total_comments"]  = 0;
                    $postArr["is_liked"]        = false;
                    $postArr["is_disliked"]     = false;
                    $postArr["post_type"]       = 2;
                    $result[]                   = $postArr;
                    if ($post->save() == false) {
                        foreach ($post->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : uploadMultipleImages : ".$errors." user_id : ".$header_data["id"]);
                        Library::output(false, '0', $errors, null);
                    }
                }
                Library::output(true, '1', POST_SAVED, $result);
            } catch (Exception $e) {
                Library::logging('error',"API : createPost : ".$e." ".": user_id : ".$header_data["id"]);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }

    function sendNotifications( $deviceToken, $message, $os ){
        if( !is_array($deviceToken) ){
            $deviceToken    = array( $deviceToken );
        }
        // prep the bundle
        if($os=='ios'){
            $file_path = dirname(__FILE__).'/certificates/ScblFinal.pem';
            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', $file_path);
            stream_context_set_option($ctx, 'ssl', 'passphrase', APN_PASSPHRASE);
            $tmpMsg = json_decode($message["message"]);
            $body['aps'] = array(
                    'alert' => $tmpMsg->message,
                    'sound' => 'default'
                    );
            $body["data"] = $message["message"];
            foreach($deviceToken as $token){
                $err    = $errstr   = '';
                $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 120, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                if(!$fp){
                    Library::logging('error',"API : sendNotifications : Unable to send push notification(Failed to connect $err $errstr) : message : ".$message["message"]);
                } 
                $payload    = json_encode($body);
                $msg        = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
                $res        = fwrite($fp, $msg);
                fclose($fp);
                if($res != 0 && $res != FALSE){
                } else {
                    Library::logging('error',"API : sendNotifications : Unable to send push notification : message : ".$message["message"]);
                }
            }
        }elseif( $os == 'android' ){
            $msg = array
            (
                    'message' 	=> $message["message"]
            );
            $fields = array
            (
                    'registration_ids' 	=> $deviceToken,
                    'data'		=> $msg
            );
            $headers = array
            (
                    'Authorization: key=' . GCM_API_ACCESS_KEY,
                    'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );
            $resultArr  = (array)json_decode($result);
            if( empty($resultArr["success"]) ){
                    Library::logging('error',"API : sendNotifications : Unable to send push notification : ($result) : message : ".$message["message"]);
           }
        }
    }

    function sendNotification($headerdata, $post_data){
        //print_r($post_data); exit("test");
            $this->sendNotifications( $post_data["token"], array("message"=>$post_data["message"]), $post_data["os"] );
    }
    
}
?>
